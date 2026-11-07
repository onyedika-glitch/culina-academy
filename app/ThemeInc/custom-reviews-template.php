<?php
/**
 * Custom Reviews page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cal_register_reviews_ui_strings', 20 );
/**
 * Register reviews page UI strings with Polylang.
 */
function cal_register_reviews_ui_strings() {
	$strings = array(
		'Your browser does not support HTML5 video.',
		'Play video testimonial from %s',
		'Video testimonial',
	);

	if ( function_exists( 'pll_register_string' ) ) {
		foreach ( $strings as $string ) {
			pll_register_string( 'reviews_ui_' . md5( $string ), $string, 'Culina Academy Reviews UI' );
		}
	}
}

add_action( 'wp_enqueue_scripts', 'cal_reviews_template_assets' );
/**
 * Load reviews template assets only where needed.
 */
function cal_reviews_template_assets() {
	if ( ! is_page_template( 'template-reviews.php' ) ) {
		return;
	}

	$css_path = get_template_directory() . '/assets/css/cal-reviews-template.css';
	$js_path  = get_template_directory() . '/assets/js/cal-reviews-template.js';

	wp_enqueue_style(
		'cal-reviews-template',
		get_template_directory_uri() . '/assets/css/cal-reviews-template.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-reviews-template',
		get_template_directory_uri() . '/assets/js/cal-reviews-template.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : GENERATE_VERSION,
		true
	);

	wp_localize_script(
		'cal-reviews-template',
		'calReviews',
		array(
			'unsupportedVideoText' => cal_i18n_text( 'Your browser does not support HTML5 video.' ),
		)
	);
}

add_filter( 'body_class', 'cal_reviews_template_body_class', 20 );
/**
 * Add template-specific body classes and normalize layout classes.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function cal_reviews_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-reviews.php' ) ) {
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
	$classes[] = 'cal-reviews-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_reviews_sidebar_layout' );
/**
 * Force the reviews template to render without sidebars.
 *
 * @param string $layout Current sidebar layout.
 * @return string
 */
function cal_reviews_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-reviews.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_reviews_template_fields' );
/**
 * Register ACF fields for the reviews page template.
 */
function cal_register_reviews_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_reviews_template',
			'title'    => 'Reviews Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_reviews_hero_tab',
					'label' => 'Hero Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_reviews_hero_title',
					'label'         => 'Hero Title',
					'name'          => 'cal_reviews_hero_title',
					'type'          => 'text',
					'default_value' => '',
				),
				array(
					'key'   => 'field_cal_reviews_header_tab',
					'label' => 'Header Section',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_reviews_heading',
					'label'         => 'Heading',
					'name'          => 'cal_reviews_heading',
					'type'          => 'text',
					'default_value' => '',
				),
				array(
					'key'   => 'field_cal_reviews_subheading',
					'label' => 'Subheading',
					'name'  => 'cal_reviews_subheading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_reviews_content_tab',
					'label' => 'Reviews',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_reviews_list',
					'label'        => 'Video Testimonials',
					'name'         => 'cal_reviews_list',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Review',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_review_name',
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_review_country',
							'label' => 'Country',
							'name'  => 'country',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_review_course',
							'label' => 'Course',
							'name'  => 'course',
							'type'  => 'text',
						),
						array(
							'key'     => 'field_cal_review_video_type',
							'label'   => 'Video Type',
							'name'    => 'video_type',
							'type'    => 'select',
							'choices' => array(
								'youtube' => 'YouTube',
								'mp4'     => 'MP4 Upload',
							),
						),
						array(
							'key'           => 'field_cal_review_youtube_id',
							'label'         => 'YouTube Video ID',
							'name'          => 'youtube_id',
							'type'          => 'text',
						),
						array(
							'key'           => 'field_cal_review_mp4_url',
							'label'         => 'MP4 Video URL',
							'name'          => 'mp4_url',
							'type'          => 'url',
						),
						array(
							'key'           => 'field_cal_review_preview',
							'label'         => 'Preview Image',
							'name'          => 'preview_image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-reviews.php',
					),
				),
			),
		)
	);
}

/**
 * Get a reviews template field value.
 *
 * @param string $name Field name.
 * @return mixed
 */
function cal_reviews_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}
