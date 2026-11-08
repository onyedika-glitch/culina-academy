<?php
/**
 * Blog page template functionality.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cal_register_blog_ui_strings', 20 );
/**
 * Register blog UI strings with Polylang.
 */
function cal_register_blog_ui_strings() {
	$strings = array(
		'Continue',
		'Load More',
		'No more posts.',
		'Invalid nonce',
	);

	if ( function_exists( 'pll_register_string' ) ) {
		foreach ( $strings as $string ) {
			pll_register_string( 'blog_ui_' . md5( $string ), $string, 'Culina Academy Blog UI' );
		}
	}
}

add_action( 'wp_enqueue_scripts', 'cal_blog_template_assets' );
/**
 * Load blog template assets only where needed.
 */
function cal_blog_template_assets() {	if ( ! is_page_template( 'template-blog.php' ) && ! is_single() ) {
		return;
	}

	wp_enqueue_style(
		'cal-blog-template',
		get_template_directory_uri() . '/assets/css/cal-blog-template.css',
		array(),
		GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-blog-template',
		get_template_directory_uri() . '/assets/js/cal-blog-template.js',
		array(),
		GENERATE_VERSION,
		true
	);

	wp_localize_script(
		'cal-blog-template',
		'calBlog',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'cal_blog_load_more' ),
			'continueText' => cal_i18n_text( 'Continue' ),
			'loadMoreText' => cal_i18n_text( 'Load More' ),
			'noMoreText'   => cal_i18n_text( 'No more posts.' ),
		)
	);
}

add_filter( 'body_class', 'cal_blog_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 */
function cal_blog_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-blog.php' ) ) {
		return $classes;
	}

	$remove = array(
		'right-sidebar',
		'left-sidebar',
		'both-sidebars',
		'both-right',
		'both-left',
		'contained-content',
	);

	$classes = array_values( array_diff( $classes, $remove ) );
	$classes[] = 'cal-blog-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_blog_sidebar_layout' );
/**
 * Force the blog template to render without sidebars.
 */
function cal_blog_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-blog.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

/**
 * Render a trimmed excerpt with a continue link.
 */
function cal_blog_card_excerpt() {
	$content = get_the_content();
	$content = strip_tags( $content );
	$trimmed = wp_trim_words( $content, 60, '&hellip;' );
	echo '<p>' . $trimmed . '</p>';
	echo '<a class="cal-blog-read-more" href="' . esc_url( get_permalink() ) . '">' . esc_html( cal_i18n_text( 'Continue' ) ) . '</a>';
}

add_action( 'wp_ajax_cal_blog_load_more', 'cal_blog_ajax_load_more' );
add_action( 'wp_ajax_nopriv_cal_blog_load_more', 'cal_blog_ajax_load_more' );
/**
 * AJAX handler for loading more blog posts.
 */
function cal_blog_ajax_load_more() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'cal_blog_load_more' ) ) {
		wp_die( cal_i18n_text( 'Invalid nonce' ) );
	}

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 2;

	$blog_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'paged'          => $page,
		)
	);

	ob_start();

	if ( $blog_query->have_posts() ) :
		while ( $blog_query->have_posts() ) :
			$blog_query->the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'cal-blog-card' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<a class="cal-blog-card-thumb" href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'medium' ); ?>
					</a>
				<?php endif; ?>
				<div class="cal-blog-card-body">
					<?php
					$categories = get_the_category();
					if ( ! empty( $categories ) ) :
						?>
						<span class="cal-blog-card-cat"><?php echo esc_html( $categories[0]->name ); ?></span>
					<?php endif; ?>
					<h3 class="cal-blog-card-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<div class="cal-blog-card-excerpt">
						<?php cal_blog_card_excerpt(); ?>
					</div>
					<div class="cal-blog-card-meta">
						<span class="cal-blog-card-date"><?php echo get_the_date(); ?></span>
					</div>
				</div>
			</article>
			<?php
		endwhile;
	endif;

	wp_reset_postdata();

	$items = ob_get_clean();

	wp_send_json(
		array(
			'items'  => $items,
			'status' => $blog_query->max_num_pages > $page ? 'have-posts' : 'no-more-posts',
		)
	);
}
