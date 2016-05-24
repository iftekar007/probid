<?php

class WPCF7_Submission {

	private static $instance;

	private $contact_form;
	private $status = 'init';
	private $posted_data = array();
	private $uploaded_files = array();
	private $skip_mail = false;
	private $response = '';
	private $invalid_fields = array();
	private $meta = array();

	private function __construct() {}

	public static function get_instance( WPCF7_ContactForm $contact_form = null ) {
		if ( empty( self::$instance ) ) {
			if ( null == $contact_form ) {
				return null;
			}

			self::$instance = new self;
			self::$instance->contact_form = $contact_form;
			self::$instance->skip_mail = $contact_form->in_demo_mode();
			self::$instance->setup_posted_data();
			self::$instance->submit();
		} elseif ( null != $contact_form ) {
			return null;
		}

		return self::$instance;
	}

	public function get_status() {
		return $this->status;
	}

	public function is( $status ) {
		return $this->status == $status;
	}

	public function get_response() {
		return $this->response;
	}

	public function get_invalid_field( $name ) {
		if ( isset( $this->invalid_fields[$name] ) ) {
			return $this->invalid_fields[$name];
		} else {
			return false;
		}
	}

	public function get_invalid_fields() {
		return $this->invalid_fields;
	}

	public function get_posted_data( $name = '' ) {
		if ( ! empty( $name ) ) {
			if ( isset( $this->posted_data[$name] ) ) {
				return $this->posted_data[$name];
			} else {
				return null;
			}
		}

		return $this->posted_data;
	}

	private function setup_posted_data() {
		$posted_data = (array) $_POST;
		$posted_data = array_diff_key( $posted_data, array( '_wpnonce' => '' ) );
		$posted_data = $this->sanitize_posted_data( $posted_data );

		$tags = $this->contact_form->form_scan_shortcode();

		foreach ( (array) $tags as $tag ) {
			if ( empty( $tag['name'] ) ) {
				continue;
			}

			$name = $tag['name'];
			$value = '';

			if ( isset( $posted_data[$name] ) ) {
				$value = $posted_data[$name];
			}

			$pipes = $tag['pipes'];

			if ( WPCF7_USE_PIPE
			&& $pipes instanceof WPCF7_Pipes
			&& ! $pipes->zero() ) {
				if ( is_array( $value) ) {
					$new_value = array();

					foreach ( $value as $v ) {
						$new_value[] = $pipes->do_pipe( wp_unslash( $v ) );
					}

					$value = $new_value;
				} else {
					$value = $pipes->do_pipe( wp_unslash( $value ) );
				}
			}

			$posted_data[$name] = $value;
		}

		$this->posted_data = apply_filters( 'wpcf7_posted_data', $posted_data );

		return $this->posted_data;
	}

	private function sanitize_posted_data( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( array( $this, 'sanitize_posted_data' ), $value );
		} elseif ( is_string( $value ) ) {
			$value = wp_check_invalid_utf8( $value );
			$value = wp_kses_no_null( $value );
		}

		return $value;
	}

	private function submit() {
		if ( ! $this->is( 'init' ) ) {
			return $this->status;
		}

		$this->meta = array(
			'remote_ip' => isset( $_SERVER['REMOTE_ADDR'] )
				? preg_replace( '/[^0-9a-f.:, ]/', '', $_SERVER['REMOTE_ADDR'] )
				: '',
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] )
				? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '',
			'url' => preg_replace( '%(?<!:|/)/.*$%', '',
				untrailingslashit( home_url() ) ) . wpcf7_get_request_uri(),
			'timestamp' => current_time( 'timestamp' ),
			'unit_tag' => isset( $_POST['_wpcf7_unit_tag'] )
				? $_POST['_wpcf7_unit_tag'] : '' );

		$contact_form = $this->contact_form;

		if ( ! $this->validate() ) { // Validation error occured
			$this->status = 'validation_failed';
			$this->response = $contact_form->message( 'validation_error' );

		} elseif ( ! $this->accepted() ) { // Not accepted terms
			$this->status = 'acceptance_missing';
			$this->response = $contact_form->message( 'accept_terms' );

		} elseif ( $this->spam() ) { // Spam!
			$this->status = 'spam';
			$this->response = $contact_form->message( 'spam' );

		} elseif ( $this->mail() ) {
			$this->status = 'mail_sent';
			$this->response = $contact_form->message( 'mail_sent_ok' );

			do_action( 'wpcf7_mail_sent', $contact_form );

		} else {
			$this->status = 'mail_failed';
			$this->response = $contact_form->message( 'mail_sent_ng' );

			do_action( 'wpcf7_mail_failed', $contact_form );
		}

		$this->remove_uploaded_files();

		return $this->status;
	}

	private function validate() {
		if ( $this->invalid_fields ) {
			return false;
		}

		require_once WPCF7_PLUGIN_DIR . '/includes/validation.php';
		$result = new WPCF7_Validation();

		$tags = $this->contact_form->form_scan_shortcode();

		foreach ( $tags as $tag ) {
			$result = apply_filters( 'wpcf7_validate_' . $tag['type'],
				$result, $tag );
		}

		$result = apply_filters( 'wpcf7_validate', $result, $tags );

		$this->invalid_fields = $result->get_invalid_fields();

		return $result->is_valid();
	}

	private function accepted() {
		return apply_filters( 'wpcf7_acceptance', true );
	}

	private function spam() {
		$spam = false;

		$user_agent = (string) $this->get_meta( 'user_agent' );

		if ( strlen( $user_agent ) < 2 ) {
			$spam = true;
		}

		if ( WPCF7_VERIFY_NONCE && ! $this->verify_nonce() ) {
			$spam = true;
		}

		if ( $this->blacklist_check() ) {
			$spam = true;
		}

		return apply_filters( 'wpcf7_spam', $spam );
	}

	private function verify_nonce() {
		return wpcf7_verify_nonce( $_POST['_wpnonce'], $this->contact_form->id() );
	}

	private function blacklist_check() {
		$target = wpcf7_array_flatten( $this->posted_data );
		$target[] = $this->get_meta( 'remote_ip' );
		$target[] = $this->get_meta( 'user_agent' );

		$target = implode( "\n", $target );

		return wpcf7_blacklist_check( $target );
	}

	/* Mail */

	private function mail() {


/*$to = "debasiskar007@gmail.com";
$subject = "Learning how to send an Email in WordPress";

$content = "<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<title>ProBidAuto</title>
</head>

<body>
<table width='100%' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td align='center' valign='middle'>
    <table width='640' border='0' cellspacing='0' cellpadding='0' style='background:#fff;'>
    
    
  <tr>
    <td style='background:#fb4a32; padding:22px;'>
    <h1 style='font-family:Arial, Helvetica, sans-serif; font-size:32px; color:#ffffff; text-transform:uppercase; text-align:center; margin:0; padding:0;'>Thank you for pre-enrolling in </h1>
    <h2 style='font-family:Arial, Helvetica, sans-serif; font-size:48px; color:#ffffff; text-transform:uppercase; text-align:center; margin:0; padding:0;'>ProBidAuto.com</h2>
    
    </td>
  </tr>
  
  
  <tr>
    <td>
   <table width='100%' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td align='left' valign='middle'><img src='http://probid.influxiq.com/wp-content/themes/probidtheme/images/mailimg1.png' /></td>
     <td align='left' valign='top' style='padding:12px;' >
     
     <div style='background:#f1f2f3; border:solid 1px #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:15px; color:#595959; line-height:22px; padding:18px;'>
     <span style='color:#32b6fb;'>ProBidAuto.com</span> <br />

New Millennium Marketing Methodology is a more superior and digitally-driven approach never before seen by the Auto Industry! 
We have seamlessly taken advantage of today’s technology shift, the alternating consumer behavior, and dealers’ need for newer marketing prowess to radically 
change the Auto Industry Marketing Methodologies.<br />
<br />


Our vision is commitment to fostering innovative, marketable, and relevant technologies to <span style='color:#fb4a32;'>ALL players in the auto industry</span>. We understood that they would only succeed in 
winning this landscaping shift is by adopting newer competencies – and we’ve 
successfully catered to the needs of the industry through ProBidAuto.com.

</div>
     </td>
  </tr>
</table>
    
    </td>
  </tr>
  
  
    
  <tr>
    <td style=' padding:0 15px;'>
    <h1 style='font-family:Arial, Helvetica, sans-serif; font-size:26px; color:#2eb0ff;  text-align:center; margin:0; padding:30px 0 0 0;'>And we’d like to welcome you to the revolution! </h1>
    <h2 style='font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#424242; text-transform:uppercase; text-align:center; margin:0; padding:10px 0;'>Getting pre-enrolled is just the tip of the iceberg</h2>
    
    
    <div style='width:100%; background:#2c2c2c; margin-top:5px; text-align:center; padding-bottom:15px;'>
    <a href='https://attendee.gotowebinar.com/register/5177406016368407042'><img src='http://probid.influxiq.com/wp-content/themes/probidtheme/images/mailimg2.png' style='width:100%;' /> </a> 

   
            
           
    </div>
    
    
    <h2 style='font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#3c3c3c; line-height:18px; padding:6px 0 6px 0; font-weight:normal; text-align:center; line-height:20px;'>Trainings are led by industry expert Michael Jackson and Visionary Internet Entrepreneur <span style='color:#fb4a32;'>Beto Paredes</span> – backed industry prowess and experience gained in the automotive marketing arena over the last 10 years. Here dealers will learn how to reinvent their marketing model and unleash new potentials by unlocking new technologies offered through ProBidAuto.com

</h2>

<h3 style='background:#2eb0ff; font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#ffffff; text-align:center; margin:0; padding:12px; font-weight:normal;'>Training starts August 6th, 2016 (then every other weekends)</h3>

<h4 style='font-family:Arial, Helvetica, sans-serif; font-size:24px; color:#fb4a32; margin:0; padding:16px 0 0 0; text-align:center;' >The ProBidAuto.com Platform Unveiling  </h4>

<label style='font-weight:normal; font-size:16px; color:#1f1f1f; font-family:Arial, Helvetica, sans-serif; display:block; text-align:center; padding:8px 0 12px 0;'>Wednesday October 19th, 2016</label>

<label style='font-weight:normal; font-size:14px; color:#1f1f1f; font-family:Arial, Helvetica, sans-serif; display:block; text-align:center; line-height:18px; padding:6px 0 6px 0'>The full tour of the ProBidAuto.com Platform is for pre-enrolled dealers only and is limited to 1,000 seats. <a href='https://attendee.gotowebinar.com/register/4431224649201867778'>Sign Up Here!</a></label>

    <div class='clear'></div>


<h3 style='background:#fb4a32; font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#ffffff; text-align:center; margin:0; padding:12px; font-weight:normal;'>The UNSTOPPABLE PROBIDAUTO.COM LAUNCH – JANUARY 2017!</h3>


<label style='font-weight:normal; font-size:14px; color:#1f1f1f; font-family:Arial, Helvetica, sans-serif; display:block; text-align:center; line-height:18px; padding:22px 0 18px 0;'>TWe lead our launch with several initial marketing campaigns that puts us and the dealers of ProBidAuto.com in front of 3 million + car buyers nationwide! </label>

<h5 style='font-family:Arial, Helvetica, sans-serif; font-size:22px; color:#2eb0ff; text-align:center; text-transform:uppercase; padding:15px 0 30px 0; margin:0;'>YOU DO NOT WANT TO MISS OUT!</h5>
    
    <img src='http://probid.influxiq.com/wp-content/themes/probidtheme/images/mailimg3.png' style='display:block; margin:0 auto;' />
    
    
    <h3 style='font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#000000; text-align:center; padding:25px 0; font-weight:normal; '>We Look Forward to Connecting You with Your Consumers Through ProBidAuto.com</h3>
    
    
    
    </td>
  </tr>
  
</table>

    
    </td>
  </tr>
</table>



</body>
</html>";
$headers[] = "From: Info <info@probidauto.com>";
$headers[] = "Content-type:  text/html" ;
$contact_form = $this->contact_form;
$mailinfo=($contact_form->prop( 'mail' )); 
$recipient = WPCF7_Mail::replace_tags( $mailinfo['recipient'] );
//print_r($recipient);
//print_r($mailinfo['recipient']);


$status = wp_mail($recipient, 'Thank you for pre-enrolling in ProBidAuto.com.
', $content,$headers);*/
//print_r($status);

		$contact_form = $this->contact_form;
		$mailinfo=($contact_form->prop( 'mail' ));
		$recipient = WPCF7_Mail::replace_tags( $mailinfo['recipient'] );
		$subject = WPCF7_Mail::replace_tags( $mailinfo['subject'] );
		$body = WPCF7_Mail::replace_tags( $mailinfo['body'] );
		$sender = WPCF7_Mail::replace_tags( $mailinfo['[sender]'] );
		$headers[] = "From: Info <info@probidauto.com>";
		$headers[] = WPCF7_Mail::replace_tags( $mailinfo['additional_headers'] );
		$headers[] = "Content-type:  text/html" ;
//		/print_r($mailinfo);
       //print_r($mailinfo['recipient']);


		$status = wp_mail($recipient, $subject, @$body,@$headers);
		//print_r($recipient);
		/*print_r($subject."gg");
		print_r($mailinfo['body']);*/
		//print_r($headers);

		//print_r($status); exit;

		
	

		$contact_form = $this->contact_form;

		do_action( 'wpcf7_before_send_mail', $contact_form );

		$skip_mail = $this->skip_mail || ! empty( $contact_form->skip_mail );
		$skip_mail = apply_filters( 'wpcf7_skip_mail', $skip_mail, $contact_form );

		if ( $skip_mail ) {
			return true;
		}

		$result = WPCF7_Mail::send( $contact_form->prop( 'mail' ), 'mail' );

		if ( $result ) {
			$additional_mail = array();

			if ( ( $mail_2 = $contact_form->prop( 'mail_2' ) ) && $mail_2['active'] ) {
				$additional_mail['mail_2'] = $mail_2;
			}

			$additional_mail = apply_filters( 'wpcf7_additional_mail',
				$additional_mail, $contact_form );

			foreach ( $additional_mail as $name => $template ) {
				WPCF7_Mail::send( $template, $name );
			}

			return true;
		}

		return false;
	}

	public function uploaded_files() {
		return $this->uploaded_files;
	}

	public function add_uploaded_file( $name, $file_path ) {
		$this->uploaded_files[$name] = $file_path;

		if ( empty( $this->posted_data[$name] ) ) {
			$this->posted_data[$name] = basename( $file_path );
		}
	}

	public function remove_uploaded_files() {
		foreach ( (array) $this->uploaded_files as $name => $path ) {
			@unlink( $path );
			@rmdir( dirname( $path ) ); // remove parent dir if it's removable (empty).
		}
	}

	public function get_meta( $name ) {
		if ( isset( $this->meta[$name] ) ) {
			return $this->meta[$name];
		}
	}
}
