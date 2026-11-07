<?php

if ( ! false && defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme textdomain.
 *
 * @return string
 */
function cal_theme_textdomain() {
    return 'generatepress';
}

/**
 * Register translation helpers after theme setup.
 */
function cal_translation_helpers_init() {
    add_action( 'acf/init', 'cal_register_translation_strings_from_options', 20 );
}
add_action( 'after_setup_theme', 'cal_translation_helpers_init' );

/**
 * Get a translated ACF field value for a post or page.
 *
 * @param string $name     ACF field name.
 * @param mixed  $fallback Fallback value when field is empty.
 * @param mixed  $post_id  Post ID or null for current post.
 *
 * @return mixed
 */
function cal_get_acf_field( $name, $fallback = '', $post_id = null ) {
    if ( ! function_exists( 'get_field' ) ) {
        return $fallback;
    }

    $value = get_field( $name, $post_id );
    if ( '' === $value || null === $value ) {
        return $fallback;
    }

    return cal_translate_acf_value( $value, $name );
}

/**
 * Get a translated ACF option page value.
 *
 * @param string $name     ACF option field name.
 * @param mixed  $fallback Fallback value when field is empty.
 *
 * @return mixed
 */
function cal_get_acf_option( $name, $fallback = '' ) {
    if ( ! function_exists( 'get_field' ) ) {
        return $fallback;
    }

    $value = get_field( $name, 'option' );
    if ( '' === $value || null === $value ) {
        return $fallback;
    }

    return cal_translate_acf_value( $value, $name );
}

/**
 * Translate an ACF value recursively.
 *
 * @param mixed  $value      ACF value.
 * @param string $field_name Field name or path.
 *
 * @return mixed
 */
function cal_translate_acf_value( $value, $field_name = '' ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $key => $item ) {
            $value[ $key ] = cal_translate_acf_value( $item, $field_name ? sprintf( '%s.%s', $field_name, $key ) : $key );
        }
        return $value;
    }

    if ( ! is_string( $value ) || '' === $value ) {
        return $value;
    }

    return cal_translate_string( $value, cal_theme_textdomain(), $field_name );
}

/**
 * Translate an individual string with support for Polylang, WPML and TranslatePress.
 *
 * @param string $string  String to translate.
 * @param string $context Translation group or context.
 * @param string $name    Unique name for WPML / string registration.
 *
 * @return string
 */
function cal_translate_string( $string, $context = '', $name = '' ) {
    if ( ! is_string( $string ) || '' === $string ) {
        return $string;
    }

    if ( function_exists( 'pll__' ) ) {
        $translated = pll__( $string );
        if ( $translated !== $string ) {
            return $translated;
        }
    }

    $context = $context ? $context : cal_theme_textdomain();
    $name    = $name ? $name : $string;

    $translated = apply_filters( 'wpml_translate_single_string', $string, $context, $name );
    if ( is_string( $translated ) && $translated !== $string ) {
        return $translated;
    }

    if ( function_exists( 'trp_translate_string' ) ) {
        $translated = trp_translate_string( $string, $context );
        if ( is_string( $translated ) && $translated !== $string ) {
            return $translated;
        }
    }

    return translate( $string, cal_theme_textdomain() );
}

/**
 * Register all option page strings with translation plugins.
 */
function cal_register_translation_strings_from_options() {
    if ( ! function_exists( 'get_fields' ) ) {
        return;
    }

    $options = get_fields( 'option' );
    if ( ! is_array( $options ) || empty( $options ) ) {
        return;
    }

    cal_register_strings_recursive( $options, 'Theme Options', 'option' );
}

/**
 * Register all ACF page field strings for the current singular page.
 */
function cal_register_translation_strings_from_page() {
    if ( ! function_exists( 'get_fields' ) ) {
        return;
    }

    if ( ! is_singular() && ! is_page_template( 'template-home.php' ) ) {
        return;
    }

    $post_id = get_queried_object_id();
    if ( ! $post_id ) {
        return;
    }

    static $registered_posts = array();
    if ( isset( $registered_posts[ $post_id ] ) ) {
        return;
    }

    $fields = get_fields( $post_id );
    if ( ! is_array( $fields ) || empty( $fields ) ) {
        $registered_posts[ $post_id ] = true;
        return;
    }

    cal_register_strings_recursive( $fields, 'ACF Page Fields', 'page_' . $post_id );
    $registered_posts[ $post_id ] = true;
}
add_action( 'wp', 'cal_register_translation_strings_from_page', 20 );

/**
 * Recursive ACF string registration helper.
 *
 * @param mixed  $value  Value to scan.
 * @param string $group  Registration group.
 * @param string $prefix Registration prefix.
 */
function cal_register_strings_recursive( $value, $group = 'Theme', $prefix = '' ) {
    if ( is_array( $value ) ) {
        foreach ( $value as $key => $item ) {
            $name = $prefix ? $prefix . '_' . sanitize_key( $key ) : sanitize_key( $key );
            cal_register_strings_recursive( $item, $group, $name );
        }
        return;
    }

    if ( ! is_string( $value ) || '' === trim( $value ) ) {
        return;
    }

    cal_register_theme_string( $prefix, $value, $group, false );
}

/**
 * Register a single string with available translation plugins.
 *
 * @param string $name      Unique string name.
 * @param string $value     The text to translate.
 * @param string $group     Translation group.
 * @param bool   $multiline Whether the string is multiline.
 */
function cal_register_theme_string( $name, $value, $group = 'Theme', $multiline = false ) {
    if ( ! is_string( $value ) || '' === trim( $value ) ) {
        return;
    }

    if ( function_exists( 'pll_register_string' ) ) {
        pll_register_string( $name, $value, $group, $multiline );
    }

    if ( function_exists( 'do_action' ) ) {
        do_action( 'wpml_register_single_string', $group, $name, $value );
    }

    if ( function_exists( 'trp_register_string' ) ) {
        trp_register_string( $name, $group, $value );
    }
}

/**
 * A wrapper for custom theme option values.
 *
 * @param string $name     ACF option field name.
 * @param mixed  $fallback Fallback value.
 *
 * @return mixed
 */
function cal_option( $name, $fallback = '' ) {
    return cal_get_acf_option( $name, $fallback );
}

/**
 * A wrapper for page ACF fields.
 *
 * @param string $name     ACF field name.
 * @param mixed  $fallback Fallback value.
 *
 * @return mixed
 */
function cal_page_field( $name, $fallback = '' ) {
    return cal_get_acf_field( $name, $fallback );
}
