<?php
get_header();


single_post_title();

while ( have_posts() ) : the_post();

	get_template_part( 'content', get_post_format() );


endwhile;


get_footer();

?>