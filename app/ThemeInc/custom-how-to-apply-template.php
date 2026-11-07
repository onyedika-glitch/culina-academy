<?php
/**
 * How to Apply page template functionality.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_how_to_apply_template_assets' );
function cal_how_to_apply_template_assets() {
	if ( ! is_page_template( 'template-how-to-apply.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-policy-pages',
		get_template_directory_uri() . '/assets/css/cal-policy-pages.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_how_to_apply_body_class', 20 );
function cal_how_to_apply_body_class( $classes ) {
	if ( ! is_page_template( 'template-how-to-apply.php' ) ) {
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
	$classes[] = 'cal-policy-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_how_to_apply_sidebar_layout' );
function cal_how_to_apply_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-how-to-apply.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_how_to_apply_fields' );
function cal_register_how_to_apply_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_cal_how_to_apply',
		'title'    => 'How to Apply Page',
		'fields'   => array(
			array(
				'key'   => 'field_cal_howto_hero_heading',
				'label' => 'Content Heading',
				'name'  => 'cal_howto_hero_heading',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_cal_howto_content',
				'label' => 'Page Content',
				'name'  => 'cal_howto_content',
				'type'  => 'wysiwyg',
				'media_upload' => false,
				'tabs'  => 'visual',
				'toolbar' => 'basic',
			),
			array(
				'key'   => 'field_cal_howto_enquire_url',
				'label' => 'Enquire Now URL',
				'name'  => 'cal_howto_enquire_url',
				'type'  => 'url',
			),
			array(
				'key'   => 'field_cal_howto_enquire_text',
				'label' => 'Enquire Now Button Text',
				'name'  => 'cal_howto_enquire_text',
				'type'  => 'text',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'template-how-to-apply.php',
				),
			),
		),
	) );
}

function cal_howto_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
}
