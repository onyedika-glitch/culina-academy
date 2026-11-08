<?php
/**
 * Custom single course page assets and ACF fields.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_single_course_assets' );
function cal_single_course_assets() {
	if ( ! is_singular( 'ca_course' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-single-course',
		get_template_directory_uri() . '/assets/css/cal-single-course.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_single_course_body_class', 20 );
function cal_single_course_body_class( $classes ) {
	if ( ! is_singular( 'ca_course' ) ) {
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
	$classes[] = 'single-ca_course';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_single_course_sidebar_layout' );
function cal_single_course_sidebar_layout( $layout ) {
	if ( is_singular( 'ca_course' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_single_course_fields' );
function cal_register_single_course_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_single_course',
			'title'    => 'Course Details',
			'fields'   => array(
				array(
					'key'   => 'field_cal_course_content_tab',
					'label' => 'Course Content',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_course_description',
					'label'        => 'Course Description',
					'name'         => 'ca_course_description',
					'type'         => 'wysiwyg',
					'instructions' => 'Editable main description shown on the single course page.',
					'tabs'         => 'all',
					'toolbar'      => 'full',
					'media_upload' => 0,
				),
				array(
					'key'   => 'field_cal_course_download_tab',
					'label' => 'Downloads & Links',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_course_download_url',
					'label'         => 'Download Programme URL',
					'name'          => 'ca_download_program_url',
					'type'          => 'url',
					'instructions'  => 'Link to the downloadable programme PDF.',
				),
				array(
					'key'           => 'field_cal_course_enquire_url',
					'label'         => 'Enquire URL',
					'name'          => 'ca_enquire_url',
					'type'          => 'url',
					'instructions'  => 'Link to the enquiry / application form.',
				),
				array(
					'key'           => 'field_cal_course_reserve_url',
					'label'         => 'Reserve / Deposit URL',
					'name'          => 'ca_reserve_url',
					'type'          => 'url',
					'instructions'  => 'Link to Stripe payment page for 10% deposit.',
				),
				array(
					'key'          => 'field_cal_course_reserve_text',
					'label'        => 'Reserve Text',
					'name'         => 'ca_reserve_text',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => 'Text displayed above the reserve button.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ca_course',
					),
				),
			),
		)
	);
}

function cal_single_course_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}

