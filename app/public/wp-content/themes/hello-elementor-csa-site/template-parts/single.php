<?php
/**
 * Custom singular template for Chestnut Square Academy pages.
 *
 * @package HelloElementorCSASite
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

