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
	<?php the_content(); ?>
	<?php
endwhile;
