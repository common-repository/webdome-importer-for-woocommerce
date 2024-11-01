<?php
/**
 * The template for displaying Archive pages.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

	<div <?php generate_do_attr( 'content' ); ?>>
		<main <?php generate_do_attr( 'main' ); ?>>
			<article class="post-11 page type-page status-publish no-featured-image-padding"><div class="inside-article">
				<?php
				/**
				 * generate_before_main_content hook.
				 *
				 * @since 0.1
				 */
				do_action( 'generate_before_main_content' );

				if (is_tax('wdwi_categories') || is_post_type_archive('wdwi_products')) {

					if ( generate_has_default_loop() ) {
						if ( have_posts() ) :

							// /**
							//  * generate_archive_title hook.
							//  *
							//  * @since 0.1
							//  *
							//  * @hooked generate_archive_title - 10
							//  */
							echo '<header class="entry-header"><h1 class="entry-title">' . single_cat_title( '', false ) . '</h1></header>';
							// do_action( 'generate_archive_title' );

							// /**
							//  * generate_before_loop hook.
							//  *
							//  * @since 3.1.0
							//  */
							do_action( 'generate_before_loop', 'archive' );

							// while ( have_posts() ) :

								WDWI_Frontend::wdwi_frontend_marktplatz();

							// endwhile;

							// /**
							//  * generate_after_loop hook.
							//  *
							//  * @since 2.3
							//  */
							// do_action( 'generate_after_loop', 'archive' );

						else :

							echo '<header class="entry-header"><h1 class="entry-title">' . single_cat_title( '', false ) . '</h1></header>';

							echo '<p>Leider sind aktuell keine Produkte in deiner gewünschten Kategorie vorhanden. Du kannst aber gerne in unserer Produktwelt weiterstöbern.</p>';

							$term_cat = get_queried_object();

							if( get_term_meta( $term_cat->term_id, 'seconddesc', true ) ) {
								echo '<div class="custom-produktkasten" id="produktdetails">';
								echo '<h2 class="custom_produktdetails_title">Produktdetails: ';
								echo $term_cat->name;
								echo '</h2>';
								echo '<div class="custom_produktdetails_p">';
								echo  wpautop ( wptexturize ( htmlspecialchars_decode( wp_trim_words ( get_term_meta( $term_cat->term_id, "seconddesc", true ), 999, "<br /><br /><a href=\"#produktdetails\">» Mehr</a>" ))));
								echo '</div>';
								echo '</div>';
							}

							// generate_do_template_part( 'none' );

						endif;
					}
				}

				/**
				 * generate_after_main_content hook.
				 *
				 * @since 0.1
				 */
				do_action( 'generate_after_main_content' );
				?>
			</div><article>
		</main>
	</div>

	<?php
	/**
	 * generate_after_primary_content_area hook.
	 *
	 * @since 2.0
	 */
	do_action( 'generate_after_primary_content_area' );

	generate_construct_sidebars();

	get_footer();
