<?php
/**
 * Custom singular template for Kiddie mock pages.
 *
 * @package HelloElementorKiddieMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

while ( have_posts() ) :
	the_post();
	?>
	<div id="content" <?php post_class( 'site-main' ); ?>>
		<?php the_content(); ?>
	</div>
	<?php
endwhile;
