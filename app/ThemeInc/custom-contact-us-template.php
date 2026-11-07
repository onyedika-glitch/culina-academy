<?php
/**
 * Contact Us page template functionality.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cal_register_contact_ui_strings', 20 );
/**
 * Register contact-page UI strings with Polylang so they appear in
 * Strings Translation and are translatable via pll__().
 */
function cal_register_contact_ui_strings() {
	$strings = array(
		'Sending...',
		'Submit now',
		'Network error. Please try again.',
		'Please fill in all required fields.',
		'Please enter a valid email address.',
		'Contact Form Message from %s',
		"Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s",
		'Thank you for your message. We will get back to you shortly.',
		'Could not send your message. Please try again later.',
	);

	if ( function_exists( 'pll_register_string' ) ) {
		foreach ( $strings as $string ) {
			pll_register_string( 'contact_ui_' . md5( $string ), $string, 'Culina Academy Contact UI' );
		}
	}
}

/**
 * Translate a contact-page UI string through Polylang when available,
 * with the theme textdomain when Polylang is unavailable.
 *
 * @param string $string Original English string.
 * @return string
 */
function cal_contact_ui_string( $string ) {
	if ( function_exists( 'pll__' ) ) {
		$translated = pll__( $string );
		if ( $translated && $translated !== $string ) {
			return $translated;
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'fr' === pll_current_language( 'slug' ) ) {
		$french_strings = array(
			'Sending...' => 'Envoi...',
			'Submit now' => 'Envoyer',
			'Network error. Please try again.' => 'Erreur réseau. Veuillez réessayer.',
			'Please fill in all required fields.' => 'Veuillez remplir tous les champs obligatoires.',
			'Please enter a valid email address.' => 'Veuillez saisir une adresse e-mail valide.',
			'Contact Form Message from %s' => 'Message du formulaire de contact de %s',
			"Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s" => "Nom : %s\nE-mail : %s\nTéléphone : %s\n\nMessage :\n%s",
			'Thank you for your message. We will get back to you shortly.' => 'Merci pour votre message. Nous vous répondrons rapidement.',
			'Could not send your message. Please try again later.' => 'Impossible d’envoyer votre message. Veuillez réessayer plus tard.',
		);

		if ( isset( $french_strings[ $string ] ) ) {
			return $french_strings[ $string ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'it' === pll_current_language( 'slug' ) ) {
		$italian_strings = array(
			'Sending...' => 'Invio...',
			'Submit now' => 'Invia',
			'Network error. Please try again.' => 'Errore di rete. Riprova.',
			'Please fill in all required fields.' => 'Compila tutti i campi obbligatori.',
			'Please enter a valid email address.' => 'Inserisci un indirizzo e-mail valido.',
			'Contact Form Message from %s' => 'Messaggio dal modulo di contatto di %s',
			"Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s" => "Nome: %s\nE-mail: %s\nTelefono: %s\n\nMessaggio:\n%s",
			'Thank you for your message. We will get back to you shortly.' => 'Grazie per il tuo messaggio. Ti risponderemo al piu presto.',
			'Could not send your message. Please try again later.' => 'Impossibile inviare il messaggio. Riprova piu tardi.',
		);

		if ( isset( $italian_strings[ $string ] ) ) {
			return $italian_strings[ $string ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'es' === pll_current_language( 'slug' ) ) {
		$spanish_strings = array(
			'Sending...' => 'Enviando...',
			'Submit now' => 'Enviar',
			'Network error. Please try again.' => 'Error de red. Inténtalo de nuevo.',
			'Please fill in all required fields.' => 'Completa todos los campos obligatorios.',
			'Please enter a valid email address.' => 'Introduce una dirección de e-mail válida.',
			'Contact Form Message from %s' => 'Mensaje del formulario de contacto de %s',
			"Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s" => "Nombre: %s\nE-mail: %s\nTeléfono: %s\n\nMensaje:\n%s",
			'Thank you for your message. We will get back to you shortly.' => 'Gracias por tu mensaje. Te responderemos pronto.',
			'Could not send your message. Please try again later.' => 'No se pudo enviar tu mensaje. Inténtalo de nuevo más tarde.',
		);

		if ( isset( $spanish_strings[ $string ] ) ) {
			return $spanish_strings[ $string ];
		}
	}

	return __( $string, 'generatepress' );
}

add_action( 'wp_enqueue_scripts', 'cal_contact_template_assets' );
function cal_contact_template_assets() {
	if ( ! is_page_template( 'template-contact-us.php' ) ) {
		return;
	}

	$css_path = get_template_directory() . '/assets/css/cal-contact-us.css';
	$js_path  = get_template_directory() . '/assets/js/cal-contact-us.js';

	wp_enqueue_style(
		'cal-contact-template',
		get_template_directory_uri() . '/assets/css/cal-contact-us.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-contact-template',
		get_template_directory_uri() . '/assets/js/cal-contact-us.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : GENERATE_VERSION,
		true
	);

	wp_localize_script( 'cal-contact-template', 'calContact', array(
		'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'cal_contact_nonce' ),
		'language'     => function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '',
		'sendingText'  => cal_contact_ui_string( 'Sending...' ),
		'submitText'   => cal_contact_ui_string( 'Submit now' ),
		'networkError' => cal_contact_ui_string( 'Network error. Please try again.' ),
	) );
}

add_filter( 'body_class', 'cal_contact_template_body_class', 20 );
function cal_contact_template_body_class( $classes ) {
	if ( ! is_page_template( 'template-contact-us.php' ) ) {
		return $classes;
	}

	$remove = array(
		'right-sidebar',
		'left-sidebar',
		'no-sidebar',
		'separate-containers',
	);

	foreach ( $remove as $class ) {
		$key = array_search( $class, $classes, true );
		if ( false !== $key ) {
			unset( $classes[ $key ] );
		}
	}

	$classes[] = 'cal-contact-template';
	$classes[] = 'no-sidebar';

	return $classes;
}

add_action( 'dynamic_sidebar_before', 'cal_contact_remove_sidebar', 0 );
function cal_contact_remove_sidebar() {
	if ( is_page_template( 'template-contact-us.php' ) ) {
		remove_all_actions( 'generate_sidebars' );
	}
}

add_action( 'wp_ajax_cal_send_contact', 'cal_contact_ajax_handler' );
add_action( 'wp_ajax_nopriv_cal_send_contact', 'cal_contact_ajax_handler' );
function cal_contact_ajax_handler() {
	check_ajax_referer( 'cal_contact_nonce', 'nonce' );
	if ( function_exists( 'cal_public_form_guard' ) ) {
		$guard = cal_public_form_guard( 'contact' );
		if ( is_wp_error( $guard ) ) {
			wp_send_json_error( $guard->get_error_message() );
		}
	}
	if ( function_exists( 'cal_switch_to_request_language' ) ) {
		cal_switch_to_request_language();
	}

	$name    = sanitize_text_field( $_POST['name'] ?? '' );
	$email   = sanitize_email( $_POST['email'] ?? '' );
	$phone   = sanitize_text_field( $_POST['phone'] ?? '' );
	$message = sanitize_textarea_field( $_POST['message'] ?? '' );

	if ( ! $name || ! $email || ! $message ) {
		wp_send_json_error( cal_contact_ui_string( 'Please fill in all required fields.' ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( cal_contact_ui_string( 'Please enter a valid email address.' ) );
	}

	$to      = get_option( 'ca_admin_email' ) ?: get_option( 'admin_email' );
	$subject = sprintf( cal_contact_ui_string( 'Contact Form Message from %s' ), $name );
	$body    = sprintf( cal_contact_ui_string( "Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s" ), $name, $email, $phone, $message );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', "Reply-To: $email" );

	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( $sent ) {
		wp_send_json_success( cal_contact_ui_string( 'Thank you for your message. We will get back to you shortly.' ) );
	} else {
		wp_send_json_error( cal_contact_ui_string( 'Could not send your message. Please try again later.' ) );
	}
}

function cal_contact_field( $key ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key );
		if ( $value ) {
			return $value;
		}

		if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
			$post_id      = get_queried_object_id();
			$default_lang = pll_default_language( 'slug' );
			$default_id   = $post_id && $default_lang ? (int) pll_get_post( $post_id, $default_lang ) : 0;

			if ( $default_id && $default_id !== (int) $post_id ) {
				$value = get_field( $key, $default_id );
				if ( $value ) {
					return $value;
				}
			}
		}
	}
	return '';
}

add_action( 'acf/include_fields', 'cal_contact_acf_fields' );
function cal_contact_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_cal_contact',
		'title'    => 'Contact Us Fields',
		'fields'   => array(
			array(
				'key'   => 'field_cal_contact_hero_title',
				'label' => 'Hero Title',
				'name'  => 'cal_contact_hero_title',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_cal_contact_address',
				'label' => 'Address',
				'name'  => 'cal_contact_address',
				'type'  => 'textarea',
				'rows'  => 4,
			),
			array(
				'key'   => 'field_cal_contact_phone',
				'label' => 'Phone Number',
				'name'  => 'cal_contact_phone',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_cal_contact_email',
				'label' => 'Email Address',
				'name'  => 'cal_contact_email',
				'type'  => 'email',
			),
			array(
				'key'   => 'field_cal_contact_hours',
				'label' => 'Opening Hours',
				'name'  => 'cal_contact_hours',
				'type'  => 'textarea',
				'rows'  => 3,
			),
			array(
				'key'   => 'field_cal_contact_form_intro',
				'label' => 'Form Intro Text',
				'name'  => 'cal_contact_form_intro',
				'type'  => 'textarea',
				'rows'  => 2,
			),
			array(
				'key'   => 'field_cal_contact_map_embed',
				'label' => 'Google Maps Embed URL',
				'name'  => 'cal_contact_map_embed',
				'type'  => 'url',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'template-contact-us.php',
				),
			),
		),
	) );
}
