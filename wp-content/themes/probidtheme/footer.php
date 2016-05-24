
<div id="footer-menu">
    <div class="container-fluid">
        <div class="footermenu">
            <div class="row">
                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                    <?php wp_nav_menu( array( 'theme_location' => 'menu_footer') ); ?>
                    <p>&copy;2016 COPY RIGHT by Probid Auto. Powered by Auto Search.</p>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="logo text-right">
                        <a href="<?php bloginfo('home'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt=""></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<script src="http://code.jquery.com/jquery.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/bootstrap.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/modernizr-2.8.3.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/common.js"></script>
<script>
    $(document).ready(function(){
        $('.carousel').carousel({
        interval: 2000,
        autoplay:1000
    });
});
</script>
<?php wp_footer(); ?>

<!-- Modal 1 start-->
<div id="thankyou_popup" class="modal fade">
<div class="modal-dialog">


<div class="modal-content">
<div class="modal-body">

<!--<button class="close" type="button" data-dismiss="modal"><img src="http://pearlhealthwp.spectrumiq.com/wp-content/themes/pearlhealth/images/popup_staff_closebtn.png" alt="" /></button>-->
<div class="thankyoutextcon_popup">
<img src="/wp-content/themes/probidtheme/images/thankyou_img1.png">
<h2> For Contacting ProBidAuto.com</h2>
<h3> A representative will contact you within 48 hours</h3>
<div class="popupfooter"><img src="/wp-content/themes/probidtheme/images/logo.png"></div>

</div>

<div class="clear"></div>
</div>

</div>
</div>
</div>

<!-- Modal 1 end-->


<!-- Modal 2 start-->
<div id="thankyou_popup1" class="modal fade">
<div class="modal-dialog">


<div class="modal-content">
<div class="modal-body">

<!--<button class="close" type="button" data-dismiss="modal">
<img src="http://pearlhealthwp.spectrumiq.com/wp-content/themes/pearlhealth/images/popup_staff_closebtn.png" alt="" />
</button>-->
<div class="thankyoutextcon_popup">
<img src="/wp-content/themes/probidtheme/images/thankyou_img1.png">
<h2>You're Successfully Pre-Enrolled With<br />
 
ProBidAuto.com</h2>

<div class="popupfooter"><img src="/wp-content/themes/probidtheme/images/logo.png"></div>

</div>

<div class="clear"></div>
</div>

</div>
</div>
</div>

<!-- Modal 2 end-->



</body>
</html>
