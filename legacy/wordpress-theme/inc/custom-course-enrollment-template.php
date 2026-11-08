<?php
/**
 * Course enrollment page template assets.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cal_register_enrollment_ui_strings', 20 );
/**
 * Register enrollment-page JS UI strings with Polylang so they appear in
 * Strings Translation and are translatable via pll__().
 */
function cal_register_enrollment_ui_strings() {
	$strings = array(
		'The booking plugin is not loaded on this page. Please make sure the Culina Academy Booking plugin is active.',
		'Please select at least one course you are interested in.',
		'Submitting...',
		'Thank you. Your application has been received.',
		'Error:',
		'Please try again.',
		'Network error. Please try again.',
	);

	if ( function_exists( 'pll_register_string' ) ) {
		foreach ( $strings as $string ) {
			pll_register_string( 'enrollment_ui_' . md5( $string ), $string, 'Culina Academy Enrollment UI' );
		}
	}
}

/**
 * Translate an enrollment-page UI string through Polylang when available.
 *
 * @param string $string Original English string.
 * @return string
 */
function cal_enrollment_ui_string( $string ) {
	if ( function_exists( 'pll__' ) ) {
		return pll__( $string );
	}
	return __( $string, 'generatepress' );
}

add_action( 'wp_enqueue_scripts', 'cal_course_enrollment_template_assets' );
function cal_course_enrollment_template_assets() {
	if ( ! is_page_template( 'template-course-enrollment.php' ) ) {
		return;
	}

	$css_path = get_template_directory() . '/assets/css/cal-course-enrollment-template.css';
	$js_path  = get_template_directory() . '/assets/js/cal-course-enrollment-template.js';

	wp_enqueue_style(
		'cal-course-enrollment-template',
		get_template_directory_uri() . '/assets/css/cal-course-enrollment-template.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-course-enrollment-template',
		get_template_directory_uri() . '/assets/js/cal-course-enrollment-template.js',
		array( 'intl-tel-js', 'ca-js' ),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : GENERATE_VERSION,
		true
	);

	wp_localize_script(
		'cal-course-enrollment-template',
		'calCourseEnrollment',
		array(
			'pluginMissing'  => cal_enrollment_ui_string( 'The booking plugin is not loaded on this page. Please make sure the Culina Academy Booking plugin is active.' ),
			'selectCourse'   => cal_enrollment_ui_string( 'Please select at least one course you are interested in.' ),
			'submitting'     => cal_enrollment_ui_string( 'Submitting...' ),
			'successMessage' => cal_enrollment_ui_string( 'Thank you. Your application has been received.' ),
			'errorPrefix'    => cal_enrollment_ui_string( 'Error:' ),
			'tryAgain'       => cal_enrollment_ui_string( 'Please try again.' ),
			'networkError'   => cal_enrollment_ui_string( 'Network error. Please try again.' ),
		)
	);
}

add_filter( 'body_class', 'cal_course_enrollment_template_body_class', 20 );
function cal_course_enrollment_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-course-enrollment.php' ) ) {
		return $classes;
	}

	$classes = array_values(
		array_diff(
			$classes,
			array(
				'right-sidebar',
				'left-sidebar',
				'both-sidebars',
				'both-right',
				'both-left',
				'contained-content',
			)
		)
	);
	$classes[] = 'cal-course-enrollment-template-page';
	$classes[] = 'no-sidebar';
	$classes[] = 'full-width-content';

	return array_unique( $classes );
}

add_filter( 'generate_sidebar_layout', 'cal_course_enrollment_template_sidebar_layout' );
function cal_course_enrollment_template_sidebar_layout( $layout ) {
	if ( is_page_template( 'template-course-enrollment.php' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}
