<?php
/**
 * Custom Our School page template fields and assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'cal_our_school_template_assets' );
function cal_our_school_template_assets() {
	if ( ! is_page_template( 'template-our-school.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'cal-our-school-template',
		get_template_directory_uri() . '/assets/css/cal-our-school-template.css',
		array(),
		GENERATE_VERSION
	);
}

add_filter( 'body_class', 'cal_our_school_template_body_class', 20 );
function cal_our_school_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-our-school.php' ) ) {
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
	$classes[] = 'cal-our-school-template-page';

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_our_school_sidebar_layout' );
function cal_our_school_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-our-school.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_action( 'acf/init', 'cal_register_our_school_template_fields' );
function cal_register_our_school_template_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_our_school_template',
			'title'    => 'Hospitality Development Page Sections',
			'fields'   => array(
				array(
					'key'   => 'field_cal_our_school_hero_tab',
					'label' => 'Hero',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_our_school_hero_title',
					'label' => 'Hero Title',
					'name'  => 'cal_our_school_hero_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_our_school_hero_kicker',
					'label' => 'Hero Kicker',
					'name'  => 'cal_our_school_hero_kicker',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_our_school_hero_intro',
					'label' => 'Hero Intro',
					'name'  => 'cal_our_school_hero_intro',
					'type'  => 'textarea',
					'rows'  => 4,
				),
				array(
					'key'           => 'field_cal_our_school_hero_image',
					'label'         => 'Hero Image',
					'name'          => 'cal_our_school_hero_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_cal_our_school_hero_cta',
					'label'         => 'Hero CTA',
					'name'          => 'cal_our_school_hero_cta',
					'type'          => 'link',
					'return_format' => 'array',
				),
				array(
					'key'   => 'field_cal_our_school_sections_tab',
					'label' => 'Development Sections',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_cal_our_school_sections',
					'label'        => 'Development Sections',
					'name'         => 'cal_our_school_sections',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add section',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_our_school_section_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_cal_our_school_section_text',
							'label' => 'Text',
							'name'  => 'text',
							'type'  => 'wysiwyg',
							'tabs'  => 'visual',
							'media_upload' => 0,
							'toolbar' => 'basic',
						),
						array(
							'key'          => 'field_cal_our_school_section_bullets',
							'label'        => 'Bullets',
							'name'         => 'bullets',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => 'Add bullet',
							'sub_fields'   => array(
								array(
									'key'   => 'field_cal_our_school_section_bullet_text',
									'label' => 'Bullet',
									'name'  => 'text',
									'type'  => 'text',
								),
							),
						),
						array(
							'key'           => 'field_cal_our_school_section_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'     => 'field_cal_our_school_section_layout',
							'label'   => 'Layout',
							'name'    => 'layout',
							'type'    => 'select',
							'choices' => array(
								'image_right' => 'Image Right',
								'image_left'  => 'Image Left',
								'wide'        => 'Wide Text',
							),
						),
					),
				),
				array(
					'key'   => 'field_cal_our_school_markets_tab',
					'label' => 'Markets & CTA',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_our_school_markets_title',
					'label' => 'Markets Title',
					'name'  => 'cal_our_school_markets_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_our_school_markets_text',
					'label' => 'Markets Text',
					'name'  => 'cal_our_school_markets_text',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'          => 'field_cal_our_school_markets',
					'label'        => 'Markets',
					'name'         => 'cal_our_school_markets',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add market',
					'sub_fields'   => array(
						array(
							'key'   => 'field_cal_our_school_market_name',
							'label' => 'Market',
							'name'  => 'name',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'   => 'field_cal_our_school_cta_title',
					'label' => 'CTA Title',
					'name'  => 'cal_our_school_cta_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_our_school_cta_text',
					'label' => 'CTA Text',
					'name'  => 'cal_our_school_cta_text',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'           => 'field_cal_our_school_cta_link',
					'label'         => 'CTA Link',
					'name'          => 'cal_our_school_cta_link',
					'type'          => 'link',
					'return_format' => 'array',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-our-school.php',
					),
				),
			),
		)
	);
}

function cal_our_school_field( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name );

		if ( '' !== $value && null !== $value ) {
			if ( 'cal_our_school_sections' === $name && ! is_array( $value ) ) {
				return cal_our_school_meta_repeater_sections( get_the_ID() );
			}

			if ( 'cal_our_school_markets' === $name && ! is_array( $value ) ) {
				return cal_our_school_meta_repeater_markets( get_the_ID() );
			}

			return $value;
		}
	}

	return '';
}

function cal_our_school_meta_repeater_sections( $post_id ) {
	$count    = (int) get_post_meta( $post_id, 'cal_our_school_sections', true );
	$sections = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$bullet_count = (int) get_post_meta( $post_id, "cal_our_school_sections_{$i}_bullets", true );
		$bullets      = array();

		for ( $j = 0; $j < $bullet_count; $j++ ) {
			$bullets[] = array(
				'text' => get_post_meta( $post_id, "cal_our_school_sections_{$i}_bullets_{$j}_text", true ),
			);
		}

		$sections[] = array(
			'title'   => get_post_meta( $post_id, "cal_our_school_sections_{$i}_title", true ),
			'text'    => get_post_meta( $post_id, "cal_our_school_sections_{$i}_text", true ),
			'bullets' => $bullets,
			'image'   => get_post_meta( $post_id, "cal_our_school_sections_{$i}_image", true ),
			'layout'  => get_post_meta( $post_id, "cal_our_school_sections_{$i}_layout", true ),
		);
	}

	return $sections;
}

function cal_our_school_meta_repeater_markets( $post_id ) {
	$count   = (int) get_post_meta( $post_id, 'cal_our_school_markets', true );
	$markets = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$markets[] = array(
			'name' => get_post_meta( $post_id, "cal_our_school_markets_{$i}_name", true ),
		);
	}

	return $markets;
}
