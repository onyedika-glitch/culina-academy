<?php
/**
 * Custom ACF-powered header and footer.
 *
 * @package GeneratePress
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'cal_custom_header_footer_remove_theme_parts', 20 );
/**
 * Remove the standard GeneratePress header/footer parts replaced by the custom layout.
 */
function cal_custom_header_footer_remove_theme_parts() {
	remove_action( 'generate_before_header', 'generate_top_bar', 5 );
	remove_action( 'generate_before_header', 'generate_add_navigation_before_header', 5 );
	remove_action( 'generate_header', 'generate_construct_header' );
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer' );
}

add_action( 'wp_enqueue_scripts', 'cal_custom_header_footer_assets', 99 );
/**
 * Load custom assets.
 */
function cal_custom_header_footer_assets() {
	$css_path = get_template_directory() . '/assets/css/cal-custom-header-footer.css';
	$js_path  = get_template_directory() . '/assets/js/cal-custom-header-footer.js';

	wp_enqueue_style(
		'cal-site-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Roboto:wght@400;500;700&family=Roboto+Slab:wght@600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cal-custom-header-footer',
		get_template_directory_uri() . '/assets/css/cal-custom-header-footer.css',
		array( 'cal-site-fonts' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : GENERATE_VERSION
	);

	wp_enqueue_script(
		'cal-custom-header-footer',
		get_template_directory_uri() . '/assets/js/cal-custom-header-footer.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : GENERATE_VERSION,
		true
	);
}

add_filter( 'generate_sidebar_layout', 'cal_force_course_sidebar_layout' );
/**
 * Force Culina Academy course pages to render without sidebars.
 *
 * @param string $layout Current GeneratePress layout.
 * @return string
 */
function cal_force_course_sidebar_layout( $layout ) {
	if ( is_singular( 'ca_course' ) ) {
		return 'no-sidebar';
	}

	return $layout;
}

add_filter( 'body_class', 'cal_course_layout_body_classes', 20 );
/**
 * Normalize body classes for Culina Academy course pages.
 *
 * @param array $classes Current body classes.
 * @return array
 */
function cal_course_layout_body_classes( $classes ) {
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

	if ( ! in_array( 'no-sidebar', $classes, true ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( ! in_array( 'full-width-content', $classes, true ) ) {
		$classes[] = 'full-width-content';
	}

	return $classes;
}

/**
 * Get the display title for the current view.
 *
 * @return string
 */
function cal_current_page_title() {
	if ( is_singular() ) {
		return get_the_title();
	}

	return wp_get_document_title();
}

/**
 * Render the shared inner-page banner.
 *
 * @param string $class_prefix Template class prefix.
 * @param string $title        Banner title.
 */
function cal_render_page_banner( $class_prefix, $title = '' ) {
	$title = $title ? $title : cal_current_page_title();
	?>
	<section class="<?php echo esc_attr( $class_prefix ); ?>-hero cal-page-hero">
		<div class="<?php echo esc_attr( $class_prefix ); ?>-hero-inner cal-page-shell">
			<h1><?php echo esc_html( $title ); ?></h1>
		</div>
	</section>
	<?php
}

/**
 * Render the shared breadcrumb pattern.
 *
 * @param string $class_name Breadcrumb class.
 * @param array  $items      Optional ancestor links.
 * @param string $current    Current item label.
 */
function cal_render_breadcrumbs( $class_name, $items = array(), $current = '' ) {
	$current = $current ? $current : cal_current_page_title();
	$home_url = apply_filters( 'culina_academy_migration_url', home_url( '/' ) );
	?>
	<nav class="<?php echo esc_attr( $class_name ); ?> cal-page-breadcrumbs" aria-label="<?php echo esc_attr( cal_i18n_text( 'Breadcrumb' ) ); ?>">
		<a href="<?php echo esc_url( $home_url ); ?>"><?php echo esc_html( cal_i18n_text( 'Home' ) ); ?></a>
		<?php foreach ( $items as $item ) : ?>
			<?php if ( empty( $item['url'] ) || empty( $item['title'] ) ) { continue; } ?>
			<span class="cal-breadcrumb-sep">&raquo;</span>
			<a href="<?php echo esc_url( apply_filters( 'culina_academy_migration_url', $item['url'] ) ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
		<?php endforeach; ?>
		<span class="cal-breadcrumb-sep">&raquo;</span>
		<span class="cal-breadcrumb-current"><?php echo esc_html( $current ); ?></span>
	</nav>
	<?php
}

add_action( 'init', 'cal_register_theme_ui_strings' );
function cal_register_theme_ui_strings() {
	$strings = array(
		'Home', 'Breadcrumb', 'Cooking Courses', 'Download programme',
		'Careers', 'Hospitality Development', 'How to Apply', 'FAQs',
		'Location', 'Duration', 'Price', 'Enquire now', 'Reserve your course now',
		'%1$s hours / %2$s', '%s hours',
		'Language switcher', 'Go to homepage',
		'Open Menu', 'Primary', 'Close Menu',
		'Open gallery image', 'Previous', 'Next', 'Close', 'Previous image', 'Next image',
		'Address', 'Phone', 'Email', 'Opening Hours:',
		'Your Name *', 'Your Email *', 'Your Phone', 'Your Message *', 'Submit now',
		'Sending...', 'Something went wrong. Please try again.', 'Network error. Please try again.',
		'Please fill in all required fields.', 'Please enter a valid email address.',
		'Contact Form Message from %s', 'Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s',
		'Thank you for your message. We will get back to you shortly.',
		'Could not send your message. Please try again later.',
		'Application Details', 'Name *', 'Surname *', 'Email *', 'Phone Number *', 'United Kingdom',
		'Address *', 'City *', 'Postcode *',
		'Which nationality is your passport from? *', 'Do you require a visa? *',
		'Yes', 'No', 'How did you hear about us? *',
		'Choose year *', 'Choose year', 'Choose month *', 'Choose month',
		'Course Preferences', 'Cookery Ability *',
		'Which Course/s are you interested in? *',
		'Why do you want to join this/these course/s? *',
		'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy.',
		'I consent to The Culina Academy collecting my details through this form. Please read our <a href="%1$s" target="_blank" rel="noopener">Cookies Policy</a> and <a href="%2$s" target="_blank" rel="noopener">Privacy Policy</a>.',
		'Submit Application',
		'Please select at least one course you are interested in.',
		'Submitting...', 'Thank you. Your application has been received.',
		'Error:', 'Please try again.',
		'January', 'February', 'March', 'April', 'May', 'June',
		'July', 'August', 'September', 'October', 'November', 'December',
		'Beginner', 'Basic Home Cook', 'Confident Home Cook', 'Professional Cook',
		'Michelin Stars:', 'Michelin Star', 'AA Rosettes:', 'AA Rosette', 'View map',
		'See course details',
		'Continue', 'Read More Reviews',
		'Read More', 'Read Less', 'Culina Academy', 'The Culina Academy',
		'Previous review', 'Next review',
		'Go to banner slide', 'Go to review', 'Previous slide', 'Next slide',
		'Your Name', 'Phone / WhatsApp', 'Submit',
		'New Course Enquiry from %s', 'Name: %s\nEmail: %s\nPhone: %s\n',
		'Thank you. We will get back to you shortly.',
		'Enquire Now',
		'Blog', 'Print',
		// Blog UI
		'Load More', 'Loading more posts...', 'No more posts.', 'Invalid nonce',
		// Reviews UI
		'Your browser does not support HTML5 video.',
	);

	// Booking/Range UI strings (used in culina-academy-booking plugin)
	$booking_strings = array(
		'Open for Enrollment', 'Limited Spots', 'Fully Booked',
		'Book Now', 'Join Waiting List', 'Join Waitlist',
		'%s places available',
		'Showing the next %s intake dates.',
		'No intake dates scheduled. Please contact us directly.',
		'No courses available right now.',
		'High in Demand',
		'Flexible weekly start',
		'Date to be confirmed',
		'Phone Number *', 'Full Name *', 'Email Address *', 'WhatsApp Number (optional)',
		'Join the waiting list to be notified and contacted if a place becomes available for this intake or if additional seats are opened.',
		'Join the waiting list to be notified and contacted if a place becomes available.',
		'Start', 'End',
		// JS i18n strings (used in enqueue_frontend wp_localize_script)
		'Network error. Please try again.',
		'Network error.',
		'Adding...',
		'You have been added to the waiting list. We will be in contact shortly.',
		'Error:',
		'Please try again.',
		'Submitting...',
		'Our team will be in contact with you shortly to discuss your application and schedule an informal telephone interview.',
		'Success',
		'Thank you for your application!',
	);

	// Contact-page UI strings (also registered by custom-contact-us-template.php)
	$contact_strings = array(
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

	// Enrollment-page UI strings (also registered by custom-course-enrollment-template.php)
	$enrollment_strings = array(
		'The booking plugin is not loaded on this page. Please make sure the Culina Academy Booking plugin is active.',
		'Please select at least one course you are interested in.',
		'Submitting...',
		'Thank you. Your application has been received.',
		'Error:',
		'Please try again.',
		'Network error. Please try again.',
	);

	// Blog UI strings (also registered by custom-blog-template.php)
	$blog_strings = array(
		'Continue',
		'Load More',
		'No more posts.',
	);

	// Reviews page UI strings (also registered by custom-reviews-template.php)
	$reviews_strings = array(
		'Your browser does not support HTML5 video.',
		'Play video testimonial from %s',
		'Video testimonial',
	);

	if ( function_exists( 'pll_register_string' ) ) {
		foreach ( $strings as $string ) {
			pll_register_string( 'theme_ui_' . sanitize_title( $string ), $string, 'Culina Academy Theme UI' );
		}
		foreach ( $booking_strings as $string ) {
			pll_register_string( 'booking_' . sanitize_title( $string ), $string, 'Culina Academy Booking' );
		}
		foreach ( $contact_strings as $string ) {
			pll_register_string( 'contact_ui_' . md5( $string ), $string, 'Culina Academy Contact UI' );
		}
		foreach ( $enrollment_strings as $string ) {
			pll_register_string( 'enrollment_ui_' . md5( $string ), $string, 'Culina Academy Enrollment UI' );
		}
		foreach ( $blog_strings as $string ) {
			pll_register_string( 'blog_ui_' . md5( $string ), $string, 'Culina Academy Blog UI' );
		}
		foreach ( $reviews_strings as $string ) {
			pll_register_string( 'reviews_ui_' . md5( $string ), $string, 'Culina Academy Reviews UI' );
		}
	}

	cal_persist_theme_ui_strings( $strings );
	cal_persist_booking_strings( $booking_strings );
	cal_persist_strings_with_context( $contact_strings, 'Culina Academy Contact UI', 'contact_ui_' );
	cal_persist_strings_with_context( $enrollment_strings, 'Culina Academy Enrollment UI', 'enrollment_ui_' );
	cal_persist_strings_with_context( $blog_strings, 'Culina Academy Blog UI', 'blog_ui_' );
	cal_persist_strings_with_context( $reviews_strings, 'Culina Academy Reviews UI', 'reviews_ui_' );
}

/**
 * Persist booking strings to polylang_wpml_strings option.
 */
function cal_persist_booking_strings( $strings ) {
	if ( ! function_exists( 'PLL' ) ) {
		return;
	}

	$option_key = 'polylang_wpml_strings';
	$existing   = get_option( $option_key, array() );
	$changed    = false;

	foreach ( $strings as $string ) {
		$hash = md5( $string );
		if ( ! isset( $existing[ $hash ] ) ) {
			$existing[ $hash ] = array(
				'context'   => 'Culina Academy Booking',
				'name'      => 'booking_' . sanitize_title( $string ),
				'string'    => $string,
				'multiline' => false,
				'icl'       => true,
			);
			$changed = true;
		}
	}

	if ( $changed ) {
		update_option( $option_key, $existing );
	}
}

/**
 * Directly persist all theme UI strings to polylang_wpml_strings option
 * so they are available for pll__() on front-end even before admin visits.
 * Uses the same MD5-hash key format that Polylang expects.
 */
function cal_persist_theme_ui_strings( $strings ) {
	if ( ! function_exists( 'PLL' ) ) {
		return;
	}

	$option_key = 'polylang_wpml_strings';
	$existing   = get_option( $option_key, array() );
	$changed    = false;

	foreach ( $strings as $string ) {
		$hash = md5( $string );
		if ( ! isset( $existing[ $hash ] ) ) {
			$existing[ $hash ] = array(
				'context'   => 'Culina Academy Theme UI',
				'name'      => 'theme_ui_' . sanitize_title( $string ),
				'string'    => $string,
				'multiline' => false,
				'icl'       => true,
			);
			$changed = true;
		}
	}

	if ( $changed ) {
		update_option( $option_key, $existing );
	}
}

/**
 * Generic helper: persist any set of strings to polylang_wpml_strings under a given context.
 *
 * @param string[] $strings   Strings to persist.
 * @param string   $context   Polylang context name, e.g. 'Culina Academy Contact UI'.
 * @param string   $prefix    Key prefix for the name field.
 */
function cal_persist_strings_with_context( $strings, $context, $prefix = 'theme_ui_' ) {
	if ( ! function_exists( 'PLL' ) ) {
		return;
	}

	$option_key = 'polylang_wpml_strings';
	$existing   = get_option( $option_key, array() );
	$changed    = false;

	foreach ( $strings as $string ) {
		$hash = md5( $string );
		if ( ! isset( $existing[ $hash ] ) ) {
			$existing[ $hash ] = array(
				'context'   => $context,
				'name'      => $prefix . md5( $string ),
				'string'    => $string,
				'multiline' => false,
				'icl'       => true,
			);
			$changed = true;
		}
	}

	if ( $changed ) {
		update_option( $option_key, $existing );
	}
}

function cal_i18n_text( $string ) {
	if ( function_exists( 'pll__' ) ) {
		$translated_value = pll__( $string );

		if ( $translated_value !== $string ) {
			return function_exists( 'cal_decode_pll_escapes' ) ? cal_decode_pll_escapes( $translated_value ) : $translated_value;
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'fr' === pll_current_language( 'slug' ) ) {
		$french_strings = array(
			'Home'               => 'Accueil',
			'Contact Us'         => 'Nous contacter',
			'Address'            => 'Adresse',
			'Phone'              => 'Téléphone',
			'Email'              => 'E-mail',
			'Opening Hours:'     => 'Horaires d’ouverture :',
			'Your Name *'        => 'Votre nom *',
			'Your Name'          => 'Votre nom',
			'Your Email *'       => 'Votre e-mail *',
			'Your Phone'         => 'Votre téléphone',
			'Phone / WhatsApp'   => 'Téléphone / WhatsApp',
			'Your Message *'     => 'Votre message *',
			'Submit now'         => 'Envoyer',
			'Download programme' => 'Télécharger le programme',
			'Download Programme' => 'Télécharger le programme',
			'Reserve your course now' => 'Réservez votre cours maintenant',
			'Enquire now'        => 'Renseignez-vous',
			'Location:'          => 'Lieu :',
			'Duration:'          => 'Durée :',
			'Price:'             => 'Prix :',
			'Location'           => 'Lieu',
			'Duration'           => 'Durée',
			'Price'              => 'Prix',
			'No intake dates scheduled. Please contact us directly.' => 'Aucune date de rentrée prévue. Veuillez nous contacter directement.',
			'Fully Booked'       => 'Complet',
			'View our courses'   => 'Voir nos cours',
			'Become a professional chef' => 'Devenez chef professionnel',
			'Contact us'         => 'Contactez-nous',
			'Name *'             => 'Prénom *',
			'Surname *'          => 'Nom *',
			'Phone Number *'     => 'Numéro de téléphone *',
			'Email *'            => 'E-mail *',
			'Postcode *'         => 'Code postal *',
			'Address *'          => 'Adresse *',
			'City *'             => 'Ville *',
			'Nationality *'      => 'Nationalité *',
			'Check Dates'        => 'Vérifier les dates',
			'Cookery Ability *'  => 'Niveau en cuisine *',
			'Do you require a visa? *' => 'Avez-vous besoin d’un visa ? *',
			'Yes'                => 'Oui',
			'No'                 => 'Non',
			'How did you hear about us? *' => 'Comment avez-vous entendu parler de nous ? *',
			'Which Course/s are you interested in? *' => 'Quel(s) cours vous intéresse(nt) ? *',
			'Why do you want to join this/these course/s? *' => 'Pourquoi souhaitez-vous suivre ce(s) cours ? *',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy .' => 'J’accepte que The Culina Academy collecte mes données via ce formulaire. Veuillez lire notre politique relative aux cookies et notre politique de confidentialité.',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy.' => 'J’accepte que The Culina Academy collecte mes données via ce formulaire. Veuillez lire notre politique relative aux cookies et notre politique de confidentialité.',
			'I consent to The Culina Academy collecting my details through this form. Please read our <a href="%1$s" target="_blank" rel="noopener">Cookies Policy</a> and <a href="%2$s" target="_blank" rel="noopener">Privacy Policy</a>.' => 'J’accepte que The Culina Academy collecte mes données via ce formulaire. Veuillez lire notre <a href="%1$s" target="_blank" rel="noopener">politique relative aux cookies</a> et notre <a href="%2$s" target="_blank" rel="noopener">politique de confidentialité</a>.',
			'Beginner'           => 'Débutant',
			'Basic Home Cook'    => 'Cuisinier amateur (base)',
			'Confident Home Cook'=> 'Cuisinier amateur (confirmé)',
			'Professional Cook'  => 'Cuisinier professionnel',
			'Michelin Stars:'    => 'Étoiles Michelin :',
			'AA Rosettes:'       => 'Rosettes AA :',
			'View map'           => 'Voir la carte',
		);

		if ( isset( $french_strings[ $string ] ) ) {
			return $french_strings[ $string ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'it' === pll_current_language( 'slug' ) ) {
		$italian_strings = array(
			'Home'               => 'Home',
			'Contact Us'         => 'Contattaci',
			'Address'            => 'Indirizzo',
			'Phone'              => 'Telefono',
			'Email'              => 'E-mail',
			'Opening Hours:'     => 'Orari di apertura:',
			'Your Name *'        => 'Il tuo nome *',
			'Your Name'          => 'Il tuo nome',
			'Your Email *'       => 'La tua e-mail *',
			'Your Phone'         => 'Il tuo telefono',
			'Phone / WhatsApp'   => 'Telefono / WhatsApp',
			'Your Message *'     => 'Il tuo messaggio *',
			'Submit now'         => 'Invia',
			'Download programme' => 'Scarica il programma',
			'Download Programme' => 'Scarica il programma',
			'Location:'          => 'Sede:',
			'Duration:'          => 'Durata:',
			'Price:'             => 'Prezzo:',
			'Location'           => 'Sede',
			'Duration'           => 'Durata',
			'Price'              => 'Prezzo',
			'Enquire now'        => 'Richiedi informazioni',
			'Reserve your course now' => 'Prenota il tuo corso ora',
			'No intake dates scheduled. Please contact us directly.' => 'Nessuna data di inizio programmata. Contattaci direttamente.',
			'Fully Booked'       => 'Al completo',
			'View our courses'   => 'Scopri i nostri corsi',
			'Become a professional chef' => 'Diventa uno chef professionista',
			'Contact us'         => 'Contattaci',
			'Name *'             => 'Nome *',
			'Surname *'          => 'Cognome *',
			'Phone Number *'     => 'Numero di telefono *',
			'Email *'            => 'E-mail *',
			'Postcode *'         => 'CAP *',
			'Address *'          => 'Indirizzo *',
			'City *'             => 'Città *',
			'Nationality *'      => 'Nazionalità *',
			'Check Dates'        => 'Controlla le date',
			'Cookery Ability *'  => 'Abilità culinaria *',
			'Do you require a visa? *' => 'Hai bisogno di un visto? *',
			'Yes'                => 'Sì',
			'No'                 => 'No',
			'How did you hear about us? *' => 'Come ci hai conosciuto? *',
			'Which Course/s are you interested in? *' => 'A quale/i corso/i sei interessato/a? *',
			'Why do you want to join this/these course/s? *' => 'Perché vuoi partecipare a questo/i corso/i? *',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy .' => 'Acconsento alla raccolta dei miei dati tramite questo modulo da parte di The Culina Academy. Si prega di leggere la nostra Cookie Policy e Privacy Policy.',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy.' => 'Acconsento alla raccolta dei miei dati tramite questo modulo da parte di The Culina Academy. Si prega di leggere la nostra politica sui cookie e informativa sulla privacy.',
			'I consent to The Culina Academy collecting my details through this form. Please read our <a href="%1$s" target="_blank" rel="noopener">Cookies Policy</a> and <a href="%2$s" target="_blank" rel="noopener">Privacy Policy</a>.' => 'Acconsento alla raccolta dei miei dati tramite questo modulo da parte di The Culina Academy. Si prega di leggere la nostra <a href="%1$s" target="_blank" rel="noopener">politica sui cookie</a> e <a href="%2$s" target="_blank" rel="noopener">informativa sulla privacy</a>.',
			'Beginner'           => 'Principiante',
			'Basic Home Cook'    => 'Cuoco amatoriale base',
			'Confident Home Cook'=> 'Cuoco amatoriale esperto',
			'Professional Cook'  => 'Cuoco professionista',
			'Michelin Stars:'    => 'Stelle Michelin:',
			'AA Rosettes:'       => 'Rosette AA:',
			'View map'           => 'Visualizza mappa',
		);

		if ( isset( $italian_strings[ $string ] ) ) {
			return $italian_strings[ $string ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'es' === pll_current_language( 'slug' ) ) {
		$spanish_strings = array(
			'Home'               => 'Inicio',
			'Contact Us'         => 'Contáctanos',
			'Address'            => 'Dirección',
			'Phone'              => 'Teléfono',
			'Email'              => 'E-mail',
			'Opening Hours:'     => 'Horario de apertura:',
			'Your Name *'        => 'Tu nombre *',
			'Your Name'          => 'Tu nombre',
			'Your Email *'       => 'Tu e-mail *',
			'Your Phone'         => 'Tu teléfono',
			'Phone / WhatsApp'   => 'Teléfono / WhatsApp',
			'Your Message *'     => 'Tu mensaje *',
			'Submit now'         => 'Enviar',
			'Download programme' => 'Descargar el programa',
			'Download Programme' => 'Descargar el programa',
			'Location:'          => 'Ubicación:',
			'Duration:'          => 'Duración:',
			'Price:'             => 'Precio:',
			'Location'           => 'Ubicación',
			'Duration'           => 'Duración',
			'Price'              => 'Precio',
			'Enquire now'        => 'Solicitar información',
			'Reserve your course now' => 'Reserva tu plaza ahora',
			'No intake dates scheduled. Please contact us directly.' => 'No hay fechas de inicio programadas. Por favor, contáctanos directamente.',
			'Fully Booked'       => 'Plazas agotadas',
			'View our courses'   => 'Ver nuestros cursos',
			'Become a professional chef' => 'Conviértete en chef profesional',
			'Contact us'         => 'Contáctanos',
			'Name *'             => 'Nombre *',
			'Surname *'          => 'Apellidos *',
			'Phone Number *'     => 'Número de teléfono *',
			'Email *'            => 'E-mail *',
			'Postcode *'         => 'Código postal *',
			'Address *'          => 'Dirección *',
			'City *'             => 'Ciudad *',
			'Nationality *'      => 'Nacionalidad *',
			'Check Dates'        => 'Consultar fechas',
			'Cookery Ability *'  => 'Habilidad culinaria *',
			'Do you require a visa? *' => '¿Necesitas visado? *',
			'Yes'                => 'Sí',
			'No'                 => 'No',
			'How did you hear about us? *' => '¿Cómo nos conociste? *',
			'Which Course/s are you interested in? *' => '¿En qué curso/s estás interesado/a? *',
			'Why do you want to join this/these course/s? *' => '¿Por qué quieres hacer este/estos curso/s? *',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy .' => 'Doy mi consentimiento para que The Culina Academy recopile mis datos a través de este formulario. Por favor, lee nuestra Política de Cookies y Política de Privacidad.',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy.' => 'Doy mi consentimiento para que The Culina Academy recopile mis datos a través de este formulario. Por favor, lee nuestra política de cookies y política de privacidad.',
			'I consent to The Culina Academy collecting my details through this form. Please read our <a href="%1$s" target="_blank" rel="noopener">Cookies Policy</a> and <a href="%2$s" target="_blank" rel="noopener">Privacy Policy</a>.' => 'Doy mi consentimiento para que The Culina Academy recopile mis datos a través de este formulario. Por favor, lee nuestra <a href="%1$s" target="_blank" rel="noopener">política de cookies</a> y <a href="%2$s" target="_blank" rel="noopener">política de privacidad</a>.',
			'Beginner'           => 'Principiante',
			'Basic Home Cook'    => 'Cocinero aficionado básico',
			'Confident Home Cook'=> 'Cocinero aficionado avanzado',
			'Professional Cook'  => 'Cocinero profesional',
			'Michelin Stars:'    => 'Estrellas Michelin:',
			'AA Rosettes:'       => 'Rosetas AA:',
			'View map'           => 'Ver mapa',
		);

		if ( isset( $spanish_strings[ $string ] ) ) {
			return $spanish_strings[ $string ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'zh' === pll_current_language( 'slug' ) ) {
		$zh_strings = array(
			'Home'               => '首页',
			'About Us'           => '关于我们',
			'Online Courses'     => '在线课程',
			'Cooking Courses'    => '烹饪课程',
			'International Students' => '国际学生',
			'Restaurant Partners' => '餐厅合作伙伴',
			'Contact Us'         => '联系我们',
			'Contact us'         => '联系我们',
			'Terms and Conditions' => '条款和条件',
			'Privacy Policy'     => '隐私政策',
			'Complaints Policy and Procedure' => '投诉政策',
			'Complaints Policy'  => '投诉政策',
			'Attendance and Punctuality Policy' => '出勤政策',
			'Attendance Policy'  => '出勤政策',
			'Cookies Policy'     => 'Cookie政策',
			'Blog'               => '博客',
			'Address'            => '地址',
			'Phone'              => '电话',
			'Email'              => '电子邮箱',
			'Opening Hours:'     => '开放时间：',
			'Your Name *'        => '您的姓名 *',
			'Your Email *'       => '您的电子邮箱 *',
			'Your Phone'         => '您的电话',
			'Your Message *'     => '您的留言 *',
			'Submit now'         => '立即提交',
			'Submit'             => '提交',
			'Send Message'       => '发送消息',
			'Read more'          => '阅读更多',
			'Learn more'         => '了解更多',
			'Apply Now'          => '立即申请',
			'Application Details' => '申请详情',
			'Course Preferences' => '课程偏好',
			'Loading more posts...' => '加载更多文章...',
			'Submit Application' => '提交申请',
			'Download programme' => '下载课程简介',
			'Download Programme' => '下载课程简介',
			'Location:'          => '地点：',
			'Duration:'          => '时长：',
			'Price:'             => '价格：',
			'Location'           => '地点',
			'Duration'           => '时长',
			'Price'              => '价格',
			'Enquire now'        => '立即咨询',
			'Reserve your course now' => '立即预订课程',
			'No intake dates scheduled. Please contact us directly.' => '暂无开课日期。请直接联系我们。',
			'No intake dates scheduled' => '暂无开课日期',
			'Fully Booked'       => '已满员',
			'Upcoming Intakes'   => '即将开课',
			'View our courses'   => '查看我们的课程',
			'Become a professional chef' => '成为专业厨师',
			'Name *'             => '名 *',
			'Surname *'          => '姓 *',
			'Phone Number *'     => '电话号码 *',
			'Email *'            => '电子邮箱 *',
			'Postcode *'         => '邮编 *',
			'Address *'          => '地址 *',
			'City *'             => '城市 *',
			'Nationality *'      => '国籍 *',
			'Check Dates'        => '查看日期',
			'Your Name'          => '您的姓名',
			'Cookery Ability *'  => '烹饪能力 *',
			'Do you require a visa? *' => '你是否需要签证？ *',
			'Yes'                => '是',
			'No'                 => '否',
			'How did you hear about us? *' => '你是如何知道我们的？ *',
			'Which Course/s are you interested in? *' => '你对哪个/哪些课程感兴趣？ *',
			'Why do you want to join this/these course/s? *' => '你为什么想参加这个/这些课程？ *',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy .' => '我同意Your City厨师学院通过此表格收集我的个人信息。请阅读我们的Cookie政策和隐私政策。',
			'I consent to The Culina Academy collecting my details through this form. Please read our <a href="%1$s" target="_blank" rel="noopener">Cookies Policy</a> and <a href="%2$s" target="_blank" rel="noopener">Privacy Policy</a>.' => '我同意Your City厨师学院通过此表格收集我的个人信息。请阅读我们的 <a href="%1$s" target="_blank" rel="noopener">Cookie政策</a> 和 <a href="%2$s" target="_blank" rel="noopener">隐私政策</a>。',
			'Beginner'           => '初学者',
			'Basic Home Cook'    => '基础家庭厨师',
			'Confident Home Cook'=> '熟练家庭厨师',
			'Professional Cook'  => '专业厨师',
			'United Kingdom'     => '英国',
			'Which nationality is your passport from? *' => '您的护照国籍是？ *',
			'Choose year *'      => '选择年份 *',
			'Choose year'        => '选择年份',
			'Choose month *'     => '选择月份 *',
			'Choose month'       => '选择月份',
			'Michelin Stars:'    => '米其林星级：',
			'AA Rosettes:'       => 'AA 玫瑰奖：',
			'View map'           => '查看地图',
		);

		if ( isset( $zh_strings[ $string ] ) ) {
			return $zh_strings[ $string ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'zh-tw' === pll_current_language( 'slug' ) ) {
		$zhtw_strings = array(
			'Home'               => '首頁',
			'About Us'           => '關於我們',
			'Online Courses'     => '線上課程',
			'Cooking Courses'    => '烹飪課程',
			'International Students' => '國際學生',
			'Restaurant Partners' => '餐廳合作夥伴',
			'Contact Us'         => '聯絡我們',
			'Contact us'         => '聯絡我們',
			'Terms and Conditions' => '條款與條件',
			'Privacy Policy'     => '隱私政策',
			'Complaints Policy and Procedure' => '投訴政策',
			'Complaints Policy'  => '投訴政策',
			'Attendance and Punctuality Policy' => '出勤政策',
			'Attendance Policy'  => '出勤政策',
			'Cookies Policy'     => 'Cookie政策',
			'Blog'               => '部落格',
			'Address'            => '地址',
			'Phone'              => '電話',
			'Email'              => '電子郵件',
			'Opening Hours:'     => '開放時間：',
			'Your Name *'        => '您的姓名 *',
			'Your Email *'       => '您的電子郵件 *',
			'Your Phone'         => '您的電話',
			'Your Message *'     => '您的留言 *',
			'Submit now'         => '立即提交',
			'Submit'             => '提交',
			'Send Message'       => '發送訊息',
			'Read more'          => '閱讀更多',
			'Learn more'         => '了解更多',
			'Apply Now'          => '立即申請',
			'Application Details' => '申請詳情',
			'Course Preferences' => '課程偏好',
			'Loading more posts...' => '載入更多文章...',
			'Submit Application' => '提交申請',
			'Download programme' => '下載課程簡介',
			'Download Programme' => '下載課程簡介',
			'Location:'          => '地點：',
			'Duration:'          => '時長：',
			'Price:'             => '價格：',
			'Location'           => '地點',
			'Duration'           => '時長',
			'Price'              => '價格',
			'Enquire now'        => '立即查詢',
			'Reserve your course now' => '立即預訂課程',
			'No intake dates scheduled. Please contact us directly.' => '暫無開課日期。請直接聯絡我們。',
			'No intake dates scheduled' => '暫無開課日期',
			'Fully Booked'       => '已額滿',
			'Upcoming Intakes'   => '即將開課',
			'View our courses'   => '查看我們的課程',
			'Become a professional chef' => '成為專業廚師',
			'Name *'             => '名 *',
			'Surname *'          => '姓 *',
			'Phone Number *'     => '電話號碼 *',
			'Email *'            => '電子郵件 *',
			'Postcode *'         => '郵遞區號 *',
			'Address *'          => '地址 *',
			'City *'             => '城市 *',
			'Nationality *'      => '國籍 *',
			'Check Dates'        => '查看日期',
			'Your Name'          => '您的姓名',
			'Cookery Ability *'  => '烹飪能力 *',
			'Do you require a visa? *' => '你是否需要簽證？ *',
			'Yes'                => '是',
			'No'                 => '否',
			'How did you hear about us? *' => '你是如何知道我們的？ *',
			'Which Course/s are you interested in? *' => '你對哪個/哪些課程感興趣？ *',
			'Why do you want to join this/these course/s? *' => '你為什麼想參加這個/這些課程？ *',
			'I consent to The Culina Academy collecting my details through this form. Please read our Cookies Policy and Privacy Policy .' => '我同意Your City廚師學院透過此表格收集我的個人資訊。請閱讀我們的Cookie政策和隱私政策。',
			'I consent to The Culina Academy collecting my details through this form. Please read our <a href="%1$s" target="_blank" rel="noopener">Cookies Policy</a> and <a href="%2$s" target="_blank" rel="noopener">Privacy Policy</a>.' => '我同意Your City廚師學院透過此表格收集我的個人資訊。請閱讀我們的 <a href="%1$s" target="_blank" rel="noopener">Cookie政策</a> 和 <a href="%2$s" target="_blank" rel="noopener">隱私政策</a>。',
			'Beginner'           => '初學者',
			'Basic Home Cook'    => '基礎家庭廚師',
			'Confident Home Cook'=> '熟練家庭廚師',
			'Professional Cook'  => '專業廚師',
			'United Kingdom'     => '英國',
			'Which nationality is your passport from? *' => '您的護照國籍是？ *',
			'Choose year *'      => '選擇年份 *',
			'Choose year'        => '選擇年份',
			'Choose month *'     => '選擇月份 *',
			'Choose month'       => '選擇月份',
			'Michelin Stars:'    => '米其林星級：',
			'AA Rosettes:'       => 'AA 玫瑰獎：',
			'View map'           => '查看地圖',
		);

		if ( isset( $zhtw_strings[ $string ] ) ) {
			return $zhtw_strings[ $string ];
		}
	}

	$value = function_exists( 'pll__' ) ? pll__( $string ) : __( $string, 'generatepress' );

	return function_exists( 'cal_decode_pll_escapes' ) ? cal_decode_pll_escapes( $value ) : $value;
}

add_filter( 'show_admin_bar', 'cal_hide_frontend_admin_bar_for_launch' );
/**
 * Keep public launch/demo pages clean when an admin is logged in.
 *
 * @param bool $show Whether to show the admin bar.
 * @return bool
 */
function cal_hide_frontend_admin_bar_for_launch( $show ) {
	return is_admin() ? $show : false;
}

add_filter( 'atfp/post/custom_fields/allowed_fields', 'cal_allow_about_template_fields_for_autopoly' );
add_filter( 'atfp/post/custom_fields/all_fields', 'cal_register_about_template_fields_for_autopoly' );
/**
 * Ensure AutoPoly sends About-template ACF text fields to the translator.
 *
 * @param array $fields AutoPoly custom-field settings.
 * @return array
 */
function cal_allow_about_template_fields_for_autopoly( $fields ) {
	foreach ( cal_about_autopoly_text_meta_keys() as $meta_key ) {
		$fields[ $meta_key ] = array(
			'status' => true,
			'type'   => 'string',
		);
	}

	return $fields;
}

/**
 * Show About-template ACF text fields as supported in AutoPoly settings.
 *
 * @param array $fields AutoPoly discovered custom fields.
 * @return array
 */
function cal_register_about_template_fields_for_autopoly( $fields ) {
	foreach ( cal_about_autopoly_text_meta_keys() as $meta_key ) {
		$fields[ $meta_key ] = array(
			'status' => 'Supported',
			'type'   => 'string',
		);
	}

	return $fields;
}

/**
 * Text meta keys from the About page template that should be machine translated.
 *
 * @return array
 */
function cal_about_autopoly_text_meta_keys() {
	$keys = array(
		'cal_about_hero_title',
		'cal_about_hero_subtitle',
		'cal_about_why_heading',
		'cal_about_why_text',
		'cal_about_features',
		'cal_about_story_heading',
		'cal_about_story_paragraphs',
		'cal_about_philosophy_heading',
		'cal_about_philosophy_text',
		'cal_about_training_heading',
		'cal_about_training_text',
		'cal_about_pathways_heading',
		'cal_about_pathways_text',
		'cal_about_cta_heading',
		'cal_about_cta_button_text',
		'cal_about_reviews_heading',
		'cal_about_reviews',
		'cal_about_reviews_button_text',
	);

	for ( $i = 0; $i < 50; $i++ ) {
		$keys[] = 'cal_about_features_' . $i . '_title';
		$keys[] = 'cal_about_features_' . $i . '_text';
		$keys[] = 'cal_about_story_paragraphs_' . $i . '_paragraph';
		$keys[] = 'cal_about_reviews_' . $i . '_name';
		$keys[] = 'cal_about_reviews_' . $i . '_description';
	}

	$site_keys = get_option( 'cal_site_autopoly_text_meta_keys', array() );
	if ( is_array( $site_keys ) ) {
		$keys = array_merge( $keys, array_filter( array_map( 'sanitize_key', $site_keys ) ) );
	}

	// Add Restaurant Partners and Careers keys
	$extra_keys = array(
		'cal_rp_hero_title',
		'cal_careers_hero_title',
		'cal_careers_intro_heading',
		'cal_careers_intro_text',
		'cal_careers_features_heading',
		'cal_careers_features_text',
	);
	for ( $i = 0; $i < 50; $i++ ) {
		$extra_keys[] = 'cal_rp_restaurants_' . $i . '_name';
		$extra_keys[] = 'cal_rp_restaurants_' . $i . '_address';
		$extra_keys[] = 'cal_rp_restaurants_' . $i . '_website_label';
		$extra_keys[] = 'cal_careers_cards_' . $i . '_title';
		$extra_keys[] = 'cal_careers_cards_' . $i . '_text';
		$extra_keys[] = 'cal_careers_cards_' . $i . '_button_text';
	}
	$keys = array_merge( $keys, $extra_keys );

	return array_values( array_unique( $keys ) );
}

add_action( 'init', 'cal_persist_about_autopoly_allowed_fields', 70 );
/**
 * Persist the allow-list so AutoPoly includes fields even before settings open.
 */
function cal_persist_about_autopoly_allowed_fields() {
	$version = '2026-06-25-1';
	if ( get_option( 'cal_about_autopoly_fields_version' ) === $version ) {
		return;
	}

	$allowed_fields = get_option( 'atfp_allowed_custom_fields', array() );
	$allowed_fields = is_array( $allowed_fields ) ? $allowed_fields : array();

	foreach ( cal_about_autopoly_text_meta_keys() as $meta_key ) {
		$allowed_fields[ $meta_key ] = array(
			'status' => true,
			'type'   => 'string',
		);
	}

	update_option( 'atfp_allowed_custom_fields', $allowed_fields, false );
	update_option( 'cal_about_autopoly_fields_version', $version, false );
}

add_action( 'save_post_page', 'cal_copy_source_page_meta_to_translation', 30, 3 );
/**
 * Copy source page structure into newly-created translated pages.
 *
 * The copy is intentionally non-destructive: existing translated ACF/page meta is
 * left alone so translation plugins can update text and media after creation.
 *
 * @param int     $post_id Page ID being saved.
 * @param WP_Post $post    Page object.
 * @param bool    $update  Whether this is an existing post update.
 */
function cal_copy_source_page_meta_to_translation( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! function_exists( 'pll_get_post_translations' ) || ! function_exists( 'pll_get_post_language' ) ) {
		return;
	}

	$lang         = pll_get_post_language( $post_id, 'slug' );
	$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';

	if ( ! $lang || ( $default_lang && $lang === $default_lang ) ) {
		return;
	}

	$translations = pll_get_post_translations( $post_id );

	$source_id = 0;
	if ( ! empty( $translations ) && is_array( $translations ) && $default_lang && ! empty( $translations[ $default_lang ] ) && (int) $translations[ $default_lang ] !== (int) $post_id ) {
		$source_id = (int) $translations[ $default_lang ];
	} else {
		if ( ! empty( $translations ) && is_array( $translations ) ) {
			foreach ( $translations as $translation_id ) {
				if ( (int) $translation_id !== (int) $post_id ) {
					$source_id = (int) $translation_id;
					break;
				}
			}
		}
	}

	if ( ! $source_id ) {
		$source_id = cal_default_language_page_source_for_template( $post_id );
	}

	if ( ! $source_id ) {
		return;
	}

	$source_meta = get_post_meta( $source_id );
	foreach ( $source_meta as $meta_key => $values ) {
		if ( ! cal_should_copy_translation_meta_key( $meta_key ) ) {
			continue;
		}

		if ( cal_translation_meta_has_value( $post_id, $meta_key ) ) {
			cal_maybe_expand_translation_repeater_count( $post_id, $meta_key, $values, $source_meta );
			continue;
		}

		cal_copy_translation_meta_values( $post_id, $meta_key, $values );
	}

	if ( '' === trim( (string) $post->post_content ) || '' === trim( (string) $post->post_excerpt ) ) {
		remove_action( 'save_post_page', 'cal_copy_source_page_meta_to_translation', 30 );

		$source_post = get_post( $source_id );
		if ( $source_post ) {
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => '' === trim( (string) $post->post_content ) ? $source_post->post_content : $post->post_content,
					'post_excerpt' => '' === trim( (string) $post->post_excerpt ) ? $source_post->post_excerpt : $post->post_excerpt,
				)
			);
		}

		add_action( 'save_post_page', 'cal_copy_source_page_meta_to_translation', 30, 3 );
	}
}

/**
 * Limit translation bootstrapping to page structure and editable ACF data.
 *
 * @param string $meta_key Meta key.
 * @return bool
 */
function cal_should_copy_translation_meta_key( $meta_key ) {
	if ( '_wp_page_template' === $meta_key || '_thumbnail_id' === $meta_key ) {
		return true;
	}

	return 0 === strpos( $meta_key, 'cal_' ) || 0 === strpos( $meta_key, '_cal_' );
}

/**
 * Copy meta values into a translation, replacing empty placeholders.
 *
 * @param int    $post_id  Post ID.
 * @param string $meta_key Meta key.
 * @param array  $values   Raw source meta values.
 */
function cal_copy_translation_meta_values( $post_id, $meta_key, $values ) {
	$first = true;

	foreach ( $values as $value ) {
		$value = maybe_unserialize( $value );

		if ( $first && metadata_exists( 'post', $post_id, $meta_key ) ) {
			update_post_meta( $post_id, $meta_key, $value );
		} else {
			add_post_meta( $post_id, $meta_key, $value );
		}

		$first = false;
	}

	cal_ensure_acf_field_reference_meta( $post_id, $meta_key );
}

/**
 * ACF repeaters store a parent row count. If a translation already has a
 * smaller count from an empty placeholder row, ACF ignores copied sub-rows.
 *
 * @param int    $post_id     Post ID.
 * @param string $meta_key    Meta key.
 * @param array  $values      Raw source meta values.
 * @param array  $source_meta All source meta values.
 */
function cal_maybe_expand_translation_repeater_count( $post_id, $meta_key, $values, $source_meta ) {
	if ( empty( $values ) || 0 === strpos( $meta_key, '_' ) || ! cal_source_meta_has_repeater_rows( $meta_key, $source_meta ) ) {
		return;
	}

	$source_count = absint( maybe_unserialize( reset( $values ) ) );
	$target_count = absint( get_post_meta( $post_id, $meta_key, true ) );

	if ( $source_count > $target_count ) {
		update_post_meta( $post_id, $meta_key, $source_count );
	}
}

/**
 * Check whether a meta key is an ACF repeater parent by looking for row keys.
 *
 * @param string $meta_key    Meta key.
 * @param array  $source_meta All source meta values.
 * @return bool
 */
function cal_source_meta_has_repeater_rows( $meta_key, $source_meta ) {
	$row_prefix = $meta_key . '_0_';

	foreach ( $source_meta as $source_key => $unused ) {
		if ( 0 === strpos( $source_key, $row_prefix ) ) {
			return true;
		}
	}

	return false;
}

add_action( 'added_post_meta', 'cal_sync_translated_acf_repeater_parent_count', 20, 4 );
add_action( 'updated_post_meta', 'cal_sync_translated_acf_repeater_parent_count', 20, 4 );
/**
 * Keep ACF repeater parent counts in step when translation plugins write rows.
 *
 * @param int    $meta_id    Meta row ID.
 * @param int    $post_id    Post ID.
 * @param string $meta_key   Meta key.
 * @param mixed  $meta_value Meta value.
 */
function cal_sync_translated_acf_repeater_parent_count( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( 0 === strpos( $meta_key, 'cal_about_' ) && cal_is_translated_page( $post_id ) ) {
		cal_ensure_acf_field_reference_meta( $post_id, $meta_key );
	}

	if ( 0 !== strpos( $meta_key, 'cal_' ) || ! preg_match( '/^(.+)_([0-9]+)_[^_].*/', $meta_key, $matches ) ) {
		return;
	}

	$parent_key = $matches[1];
	$row_count  = absint( $matches[2] ) + 1;

	if ( ! $parent_key || $row_count < 1 || ! cal_is_translated_page( $post_id ) ) {
		return;
	}

	$current_count = absint( get_post_meta( $post_id, $parent_key, true ) );
	if ( $row_count > $current_count ) {
		update_post_meta( $post_id, $parent_key, $row_count );
	}

	cal_ensure_acf_field_reference_meta( $post_id, $parent_key );
	cal_ensure_acf_field_reference_meta( $post_id, $meta_key );
}

/**
 * Check whether a post is a non-default-language page.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function cal_is_translated_page( $post_id ) {
	if ( 'page' !== get_post_type( $post_id ) || ! function_exists( 'pll_get_post_language' ) ) {
		return false;
	}

	$lang         = pll_get_post_language( $post_id, 'slug' );
	$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';

	return $lang && ( ! $default_lang || $lang !== $default_lang );
}

/**
 * Find the default-language page using the same template as a translated page.
 *
 * @param int $post_id Translated page ID.
 * @return int
 */
function cal_default_language_page_source_for_template( $post_id ) {
	if ( 'page' !== get_post_type( $post_id ) || ! function_exists( 'pll_get_post_language' ) ) {
		return 0;
	}

	$template = get_page_template_slug( $post_id );
	if ( ! $template || 'default' === $template ) {
		return 0;
	}

	$default_lang = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
	if ( ! $default_lang ) {
		return 0;
	}

	$page_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
		)
	);

	foreach ( $page_ids as $page_id ) {
		if ( (int) $page_id !== (int) $post_id && $default_lang === pll_get_post_language( $page_id, 'slug' ) ) {
			return (int) $page_id;
		}
	}

	return 0;
}

/**
 * Ensure ACF can resolve a local field value by name in translated editors.
 *
 * @param int    $post_id  Post ID.
 * @param string $meta_key Value meta key.
 */
function cal_ensure_acf_field_reference_meta( $post_id, $meta_key ) {
	$field_key = cal_acf_field_reference_for_meta_key( $meta_key );
	if ( ! $field_key ) {
		return;
	}

	$reference_key = '_' . $meta_key;
	if ( ! metadata_exists( 'post', $post_id, $reference_key ) ) {
		update_post_meta( $post_id, $reference_key, $field_key );
	}
}

/**
 * Map known local ACF value meta keys to their field keys.
 *
 * @param string $meta_key Value meta key.
 * @return string
 */
function cal_acf_field_reference_for_meta_key( $meta_key ) {
	$map = array(
		'cal_about_hero_title'           => 'field_cal_about_hero_title',
		'cal_about_hero_subtitle'        => 'field_cal_about_hero_subtitle',
		'cal_about_hero_image'           => 'field_cal_about_hero_image',
		'cal_about_why_heading'          => 'field_cal_about_why_heading',
		'cal_about_why_text'             => 'field_cal_about_why_text',
		'cal_about_features'             => 'field_cal_about_features',
		'cal_about_story_heading'        => 'field_cal_about_story_heading',
		'cal_about_story_paragraphs'     => 'field_cal_about_story_paragraphs',
		'cal_about_story_video'          => 'field_cal_about_story_video',
		'cal_about_philosophy_icon'      => 'field_cal_about_philosophy_icon',
		'cal_about_philosophy_heading'   => 'field_cal_about_philosophy_heading',
		'cal_about_philosophy_text'      => 'field_cal_about_philosophy_text',
		'cal_about_training_icon'        => 'field_cal_about_training_icon',
		'cal_about_training_heading'     => 'field_cal_about_training_heading',
		'cal_about_training_text'        => 'field_cal_about_training_text',
		'cal_about_pathways_icon'        => 'field_cal_about_pathways_icon',
		'cal_about_pathways_heading'     => 'field_cal_about_pathways_heading',
		'cal_about_pathways_text'        => 'field_cal_about_pathways_text',
		'cal_about_cta_heading'          => 'field_cal_about_cta_heading',
		'cal_about_cta_button_text'      => 'field_cal_about_cta_button_text',
		'cal_about_cta_button_url'       => 'field_cal_about_cta_button_url',
		'cal_about_reviews_heading'      => 'field_cal_about_reviews_heading',
		'cal_about_reviews'              => 'field_cal_about_reviews',
		'cal_about_reviews_button_text'  => 'field_cal_about_reviews_button_text',
		'cal_about_reviews_button_url'   => 'field_cal_about_reviews_button_url',
	);

	if ( isset( $map[ $meta_key ] ) ) {
		return $map[ $meta_key ];
	}

	if ( preg_match( '/^cal_about_story_paragraphs_[0-9]+_paragraph$/', $meta_key ) ) {
		return 'field_cal_about_story_paragraph';
	}

	if ( preg_match( '/^cal_about_features_[0-9]+_title$/', $meta_key ) ) {
		return 'field_cal_about_feature_title';
	}

	if ( preg_match( '/^cal_about_features_[0-9]+_text$/', $meta_key ) ) {
		return 'field_cal_about_feature_text';
	}

	if ( preg_match( '/^cal_about_features_[0-9]+_icon$/', $meta_key ) ) {
		return 'field_cal_about_feature_icon';
	}

	if ( preg_match( '/^cal_about_reviews_[0-9]+_name$/', $meta_key ) ) {
		return 'field_cal_about_review_name';
	}

	if ( preg_match( '/^cal_about_reviews_[0-9]+_description$/', $meta_key ) ) {
		return 'field_cal_about_review_description';
	}

	if ( preg_match( '/^cal_about_reviews_[0-9]+_image_url$/', $meta_key ) ) {
		return 'field_cal_about_review_image_url';
	}

	if ( preg_match( '/^cal_about_reviews_[0-9]+_video_url$/', $meta_key ) ) {
		return 'field_cal_about_review_video_url';
	}

	return '';
}

/**
 * Ensure missing ACF field references exist for translated About fields.
 *
 * @param int $post_id Post ID.
 */
function cal_repair_about_story_field_references( $post_id ) {
	$meta = get_post_meta( $post_id );
	if ( empty( $meta ) || ! is_array( $meta ) ) {
		return;
	}

	foreach ( array_keys( $meta ) as $meta_key ) {
		if ( 0 === strpos( $meta_key, 'cal_about_' ) ) {
			cal_ensure_acf_field_reference_meta( $post_id, $meta_key );
		}
	}
}

/**
 * Check whether a translated page already has a meaningful meta value.
 *
 * @param int    $post_id  Post ID.
 * @param string $meta_key Meta key.
 * @return bool
 */
function cal_translation_meta_has_value( $post_id, $meta_key ) {
	$values = get_post_meta( $post_id, $meta_key, false );
	if ( empty( $values ) ) {
		return false;
	}

	foreach ( $values as $value ) {
		if ( is_array( $value ) && ! empty( $value ) ) {
			return true;
		}

		if ( is_scalar( $value ) && '' !== trim( (string) $value ) && '0' !== trim( (string) $value ) ) {
			return true;
		}
	}

	return false;
}

add_action( 'init', 'cal_repair_translated_page_acf_meta', 60 );
/**
 * Repair existing translated pages whose ACF repeater row counts were created
 * before the safer translation meta copier was in place.
 */
function cal_repair_translated_page_acf_meta() {
	$repair_version = '2026-06-21-1';
	if ( get_option( 'cal_translated_page_acf_meta_repair' ) === $repair_version || ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_get_post_translations' ) ) {
		return;
	}

	$page_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	foreach ( $page_ids as $page_id ) {
		$page = get_post( $page_id );
		if ( $page ) {
			cal_copy_source_page_meta_to_translation( $page_id, $page, true );
			cal_repair_translated_page_repeater_counts( $page_id );
			cal_repair_about_story_field_references( $page_id );
		}
	}

	update_option( 'cal_translated_page_acf_meta_repair', $repair_version, false );
}

/**
 * Repair repeater parent counts from already-translated row meta.
 *
 * @param int $post_id Post ID.
 */
function cal_repair_translated_page_repeater_counts( $post_id ) {
	if ( ! cal_is_translated_page( $post_id ) ) {
		return;
	}

	$meta = get_post_meta( $post_id );
	if ( empty( $meta ) || ! is_array( $meta ) ) {
		return;
	}

	$counts = array();
	foreach ( $meta as $meta_key => $values ) {
		if ( 0 !== strpos( $meta_key, 'cal_' ) || ! preg_match( '/^(.+)_([0-9]+)_[^_].*/', $meta_key, $matches ) ) {
			continue;
		}

		$parent_key = $matches[1];
		$row_count  = absint( $matches[2] ) + 1;
		$counts[ $parent_key ] = max( $counts[ $parent_key ] ?? 0, $row_count );
	}

	foreach ( $counts as $parent_key => $row_count ) {
		if ( $row_count > absint( get_post_meta( $post_id, $parent_key, true ) ) ) {
			update_post_meta( $post_id, $parent_key, $row_count );
		}
	}
}

add_action( 'acf/init', 'cal_register_header_footer_options' );
/**
 * Register the ACF options page and local fields.
 */
function cal_register_header_footer_options() {
	if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => 'Theme Header & Footer',
			'menu_title' => 'Header & Footer',
			'menu_slug'  => 'cal-header-footer',
			'capability' => 'edit_posts',
			'redirect'   => false,
			'position'   => 59,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_cal_header_footer',
			'title'    => 'Theme Header & Footer',
			'fields'   => array(
				array(
					'key'   => 'field_cal_header_tab',
					'label' => 'Header',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_cal_header_logo',
					'label'         => 'Logo',
					'name'          => 'cal_header_logo',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_cal_header_mobile_logo',
					'label'         => 'Mobile Menu Logo',
					'name'          => 'cal_header_mobile_logo',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'   => 'field_cal_header_phone',
					'label' => 'Phone',
					'name'  => 'cal_header_phone',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_header_email',
					'label' => 'Email',
					'name'  => 'cal_header_email',
					'type'  => 'email',
				),
				array(
					'key'   => 'field_cal_footer_tab',
					'label' => 'Footer',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_cal_footer_cta_title',
					'label' => 'CTA Title',
					'name'  => 'cal_footer_cta_title',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_cal_footer_cta_link',
					'label'         => 'CTA Link',
					'name'          => 'cal_footer_cta_link',
					'type'          => 'link',
					'return_format' => 'array',
				),
				array(
					'key'           => 'field_cal_footer_cta_background',
					'label'         => 'CTA Background',
					'name'          => 'cal_footer_cta_background',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'   => 'field_cal_footer_gallery_heading',
					'label' => 'Gallery Heading',
					'name'  => 'cal_footer_gallery_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_footer_gallery',
					'label'        => 'Gallery',
					'name'         => 'cal_footer_gallery',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add image',
					'sub_fields'   => array(
						array(
							'key'           => 'field_cal_footer_gallery_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						),
					),
				),
				array(
					'key'   => 'field_cal_footer_about_heading',
					'label' => 'About Heading',
					'name'  => 'cal_footer_about_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_footer_about',
					'label' => 'About Text',
					'name'  => 'cal_footer_about',
					'type'  => 'textarea',
					'rows'  => 4,
				),
				array(
					'key'   => 'field_cal_footer_contact_heading',
					'label' => 'Contact Heading',
					'name'  => 'cal_footer_contact_heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_footer_address',
					'label' => 'Address',
					'name'  => 'cal_footer_address',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'   => 'field_cal_footer_phone',
					'label' => 'Footer Phone',
					'name'  => 'cal_footer_phone',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cal_footer_email',
					'label' => 'Footer Email',
					'name'  => 'cal_footer_email',
					'type'  => 'email',
				),
				array(
					'key'   => 'field_cal_footer_policies_heading',
					'label' => 'Policies Heading',
					'name'  => 'cal_footer_policies_heading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_cal_footer_policy_links',
					'label'        => 'Policy Links',
					'name'         => 'cal_footer_policy_links',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add link',
					'sub_fields'   => array(
						array(
							'key'           => 'field_cal_footer_policy_link',
							'label'         => 'Link',
							'name'          => 'link',
							'type'          => 'link',
							'return_format' => 'array',
						),
					),
				),
				array(
					'key'   => 'field_cal_footer_bottom_line',
					'label' => 'Bottom Line',
					'name'  => 'cal_footer_bottom_line',
					'type'  => 'textarea',
					'rows'  => 2,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'cal-header-footer',
					),
				),
			),
		)
	);
}

add_action( 'generate_before_header', 'cal_render_custom_header', 3 );
/**
 * Render the custom fixed header.
 */
function cal_render_custom_header() {
	$phone     = cal_option( 'cal_header_phone' );
	$email     = cal_option( 'cal_header_email' );
	$logo            = cal_image_url( cal_option( 'cal_header_logo' ) );
	$language_output = cal_get_language_output();
	?>
	<?php
	// ── Mobile/tablet flags bar (visible on ≤1024px, above the main header) ──
	if ( $language_output ) :
		$_langs = cal_get_language_links();
		if ( ! empty( $_langs ) ) :
	?>
	<div class="crisp-nav-mobile-flags-bar" role="navigation" aria-label="<?php echo esc_attr( cal_i18n_text( 'Language switcher' ) ); ?>">
		<div class="crisp-nav-mobile-flags-right">
			<?php foreach ( $_langs as $_l ) :
				$_flag  = cal_hd_flag_url( $_l['code'] ?? '', cal_image_url( $_l['flag'] ?? '' ) );
				$_url   = $_l['url'] ?? '';
				$_label = $_l['label'] ?? '';
				$_code  = $_l['code'] ?? '';
				if ( ! $_flag || ! $_url ) continue;
			?>
				<a href="<?php echo esc_url( $_url ); ?>"
				   hreflang="<?php echo esc_attr( $_code ); ?>"
				   lang="<?php echo esc_attr( $_code ); ?>"
				   aria-label="<?php echo esc_attr( $_label ); ?>">
					<img src="<?php echo esc_url( $_flag ); ?>" alt="<?php echo esc_attr( $_label ); ?>" width="40" height="30" loading="eager">
				</a>
			<?php endforeach; ?>
		</div>
		<div class="crisp-nav-mobile-flags-left">
			<?php if ( $phone ) : ?>
				<span class="crisp-nav-mobile-flags-phone"><?php echo cal_phone_icon(); ?><?php echo esc_html( $phone ); ?></span>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<span class="crisp-nav-mobile-flags-email"><?php echo cal_email_icon(); ?><?php echo esc_html( $email ); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php
		endif;
	endif;
	?>
	<div class="crisp-nav">
		<?php if ( $phone || $email || $language_output ) : ?>
			<div class="crisp-nav-topbar">
				<div class="crisp-nav-topbar-container">
					<?php if ( $phone || $email ) : ?>
						<ul class="crisp-nav-contact-info">
							<?php if ( $phone ) : ?>
								<li><?php echo cal_phone_icon(); ?><?php echo esc_html( $phone ); ?></li>
							<?php endif; ?>
							<?php if ( $email ) : ?>
								<li><?php echo cal_email_icon(); ?><?php echo esc_html( $email ); ?></li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
					<?php echo $language_output; ?>
				</div>
			</div>
		<?php endif; ?>

		<header class="crisp-nav-header">
			<div class="crisp-nav-container">
				<?php if ( $logo ) : ?>
					<div class="crisp-nav-logo">
						<a href="<?php echo esc_url( apply_filters( 'culina_academy_migration_url', home_url( '/' ) ) ); ?>" class="crisp-nav-logo-link" aria-label="<?php echo esc_attr( cal_i18n_text( 'Go to homepage' ) ); ?>">
							<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						</a>
					</div>
				<?php endif; ?>
				<button class="crisp-nav-mobile-toggle" aria-label="<?php echo esc_attr( cal_i18n_text( 'Open Menu' ) ); ?>">
					<?php echo cal_menu_icon(); ?>
				</button>
				<nav class="crisp-nav-nav" aria-label="<?php echo esc_attr( cal_i18n_text( 'Primary' ) ); ?>">
					<button class="crisp-nav-mobile-close" aria-label="<?php echo esc_attr( cal_i18n_text( 'Close Menu' ) ); ?>">
						<?php echo cal_close_icon(); ?>
					</button>
					<?php cal_render_primary_wp_menu(); ?>
				</nav>
				<div class="crisp-nav-mobile-overlay"></div>
			</div>
		</header>
	</div>
	<?php
}

add_action( 'generate_before_footer', 'cal_render_custom_footer' );
/**
 * Render the custom footer.
 */
function cal_render_custom_footer() {
	$cta_title      = cal_option( 'cal_footer_cta_title' );
	$cta_link       = cal_option( 'cal_footer_cta_link' );
	$cta_background = cal_image_url( cal_option( 'cal_footer_cta_background' ) );
	$gallery_title  = cal_option( 'cal_footer_gallery_heading' );
	$gallery        = cal_option( 'cal_footer_gallery' );
	$about_title    = cal_option( 'cal_footer_about_heading' );
	$about          = cal_option( 'cal_footer_about' );
	$contact_title  = cal_option( 'cal_footer_contact_heading' );
	$address        = cal_option( 'cal_footer_address' );
	$phone          = cal_option( 'cal_footer_phone' );
	$email          = cal_option( 'cal_footer_email' );
	$policies_title = cal_option( 'cal_footer_policies_heading' );
	$policy_links   = cal_option( 'cal_footer_policy_links' );
	$bottom_line    = cal_option( 'cal_footer_bottom_line' );
	$render_link    = 'cal_render_link';
	$cta_link       = is_array( $cta_link ) ? $cta_link : array();
	$gallery        = is_array( $gallery ) ? $gallery : array();
	$policy_links   = is_array( $policy_links ) ? $policy_links : array();

	?>
	<div class="cal-custom-footer">
		<?php if ( $cta_title || ! empty( $cta_link['url'] ) || $cta_background ) : ?>
			<section class="cal-footer-cta">
				<?php if ( $cta_background ) : ?>
					<img class="cal-footer-cta-bg" src="<?php echo esc_url( $cta_background ); ?>" alt="" loading="lazy">
				<?php endif; ?>
				<div class="cal-footer-cta-content">
					<?php if ( $cta_title ) : ?>
						<h2><?php echo esc_html( $cta_title ); ?></h2>
					<?php endif; ?>
					<?php $render_link( $cta_link, 'cal-footer-cta-button' ); ?>
				</div>
			</section>
		<?php endif; ?>

		<footer class="cal-footer-main">
			<div class="cal-footer-container">
				<?php if ( $gallery_title || ! empty( $gallery ) ) : ?>
					<div class="cal-footer-column">
						<?php if ( $gallery_title ) : ?>
							<h4><?php echo esc_html( $gallery_title ); ?></h4>
						<?php endif; ?>
						<?php if ( ! empty( $gallery ) ) : ?>
							<div class="cal-gallery-slider" data-total="<?php echo count($gallery); ?>" data-visible="3">
								<div class="cal-gallery-track">
									<?php foreach ( $gallery as $gallery_item ) : ?>
										<?php
										$image     = $gallery_item['image'] ?? $gallery_item;
										$image_url = cal_image_url( $image );
										$image_alt = is_array( $image ) && ! empty( $image['alt'] ) ? $image['alt'] : '';
										?>
										<?php if ( $image_url ) : ?>
											<div class="cal-gallery-slide">
												<a href="<?php echo esc_url( $image_url ); ?>" class="cal-gallery-lightbox-link" aria-label="<?php echo esc_attr( $image_alt ?: cal_i18n_text( 'Open gallery image' ) ); ?>">
													<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" width="89" height="89" loading="lazy" decoding="async">
												</a>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
								<button class="cal-gallery-prev" type="button" aria-label="<?php echo esc_attr( cal_i18n_text( 'Previous' ) ); ?>"></button>
								<button class="cal-gallery-next" type="button" aria-label="<?php echo esc_attr( cal_i18n_text( 'Next' ) ); ?>"></button>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $about_title || $about ) : ?>
					<div class="cal-footer-column">
						<?php if ( $about_title ) : ?>
							<h4><?php echo esc_html( $about_title ); ?></h4>
						<?php endif; ?>
						<?php if ( $about ) : ?>
							<p><?php echo esc_html( $about ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $contact_title || $address || $phone || $email ) : ?>
					<div class="cal-footer-column">
						<?php if ( $contact_title ) : ?>
							<h4><?php echo esc_html( $contact_title ); ?></h4>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<div class="cal-contact-item"><a href="<?php echo esc_url( cal_footer_map_url( $address ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $address ); ?></a></div>
						<?php endif; ?>
						<?php if ( $phone ) : ?>
							<div class="cal-contact-item"><?php echo esc_html( $phone ); ?></div>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<div class="cal-contact-item"><?php echo esc_html( $email ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $policies_title || ! empty( $policy_links ) ) : ?>
					<div class="cal-footer-column">
						<?php if ( $policies_title ) : ?>
							<h4><?php echo esc_html( $policies_title ); ?></h4>
						<?php endif; ?>
						<?php if ( ! empty( $policy_links ) ) : ?>
							<ul class="cal-footer-links">
								<?php foreach ( $policy_links as $policy_link ) : ?>
									<li><?php $render_link( $policy_link['link'] ?? $policy_link ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( $bottom_line ) : ?>
				<div class="cal-footer-bottom-line"><?php echo esc_html( $bottom_line ); ?></div>
			<?php endif; ?>
		</footer>
		<div class="cal-gallery-lightbox" aria-hidden="true">
			<button class="cal-gallery-lightbox-close" type="button" aria-label="<?php echo esc_attr( cal_i18n_text( 'Close' ) ); ?>"></button>
			<button class="cal-gallery-lightbox-prev" type="button" aria-label="<?php echo esc_attr( cal_i18n_text( 'Previous image' ) ); ?>"></button>
			<div class="cal-gallery-lightbox-image-wrap">
				<img src="" alt="">
			</div>
			<button class="cal-gallery-lightbox-next" type="button" aria-label="<?php echo esc_attr( cal_i18n_text( 'Next image' ) ); ?>"></button>
		</div>
	</div>
	<?php
}

/**
 * Get an ACF option value.
 */
function cal_option( $name ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name, 'option' );

		if ( ! empty( $value ) ) {
			return cal_translate_header_footer_value( $name, $value );
		}
	}

	return '';
}

/**
 * Register and retrieve ACF option strings for multilingual plugins when present.
 *
 * This keeps the theme neutral: TranslatePress/GTranslate can still read the
 * rendered frontend, while WPML/Polylang can also discover option strings.
 */
function cal_translate_header_footer_value( $name, $value ) {
	if ( is_string( $value ) ) {
		return cal_translate_header_footer_string( $name, $value );
	}

	return $value;
}

function cal_translate_header_footer_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return $value;
	}

	// These launch labels are intentional navigation copy. Keep them ahead of
	// stale Polylang string records imported from earlier footer wording.
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	$launch_labels = array(
		'fr' => array(
			'link_complaintspolicyandprocedure' => 'Politique de réclamations',
			'link_attendanceandpunctualitypolicy' => 'Politique de présence',
			'link_blog' => 'Blog',
			'link_contactus' => 'Contactez-nous',
		),
		'es' => array(
			'link_blog' => 'Blog',
		),
		'it' => array(
			'link_blog' => 'Blog',
		),
		'zh' => array(
			'cal_footer_cta_title' => '成为专业厨师',
			'cal_footer_gallery_heading' => '图片展示',
			'cal_footer_about_heading' => 'The Culina Academy',
			'cal_footer_about' => 'Culina Academy 为厨师、糕点师和餐厅经理提供实践型专业培训。请预约我们的顾问以获取更多信息。',
			'cal_footer_contact_heading' => '联系我们',
			'cal_footer_policies_heading' => '我们的政策',
			'cal_footer_bottom_line' => 'The Culina Academy 在Your City提供专业厨师培训，通过高水准厨房中的实践学习，帮助学生培养真正的专业技能。',
			'link_viewourcourses' => '查看我们的课程',
			'link_termsandconditions' => '条款和条件',
			'link_privacypolicy' => '隐私政策',
			'link_complaintspolicyandprocedure' => '投诉政策与程序',
			'link_attendanceandpunctualitypolicy' => '出勤政策',
			'link_cookiespolicy' => 'Cookie 政策',
			'link_blog' => '博客',
			'link_contactus' => '联系我们',
		),
		'zh-tw' => array(
			'cal_footer_cta_title' => '成為專業主廚',
			'cal_footer_gallery_heading' => '圖片展示',
			'cal_footer_about_heading' => 'The Culina Academy',
			'cal_footer_about' => 'Culina Academy 為廚師、糕點師和餐廳經理提供實踐型專業培訓。請預約我們的顧問以獲取更多資訊。',
			'cal_footer_contact_heading' => '聯絡我們',
			'cal_footer_policies_heading' => '我們的政策',
			'cal_footer_bottom_line' => 'The Culina Academy 在Your City提供專業主廚培訓，透過高水準廚房中的實務學習，協助學生培養真正的專業技能。',
			'link_viewourcourses' => '查看我們的課程',
			'link_termsandconditions' => '條款與條件',
			'link_privacypolicy' => '隱私權政策',
			'link_complaintspolicyandprocedure' => '投訴政策與程序',
			'link_attendanceandpunctualitypolicy' => '出席政策',
			'link_cookiespolicy' => 'Cookie 政策',
			'link_blog' => '部落格',
			'link_contactus' => '聯絡我們',
		),
	);
	if ( isset( $launch_labels[ $lang ][ $name ] ) ) {
		return $launch_labels[ $lang ][ $name ];
	}

	if ( function_exists( 'pll_current_language' ) && 'zh' === pll_current_language( 'slug' ) ) {
		$zh_values = array(
			'cal_footer_cta_title'         => '成为专业厨师',
			'cal_footer_gallery_heading'   => '图库',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy 为厨师、糕点师和餐厅经理提供实践型专业培训。请预约我们的顾问以获取更多信息。',
			'cal_footer_contact_heading'   => '联系我们',
			'cal_footer_address'           => 'The Culina Academy LTD — 注册地址：123 Example Street, Your City',
			'cal_footer_policies_heading'  => '我们的政策',
			'cal_footer_bottom_line'       => 'The Culina Academy 在Your City提供专业厨师培训，通过高水准厨房中的实践学习培养专业技能。',
			'link_viewourcourses'          => '查看我们的课程',
			'link_termsandconditions'      => '条款和条件',
			'link_privacypolicy'           => '隐私政策',
			'link_complaintspolicyandprocedure' => '投诉政策',
			'link_attendanceandpunctualitypolicy' => '出勤政策',
			'link_cookiespolicy'           => 'Cookie政策',
			'link_blog'                    => '博客',
			'link_contactus'               => '联系我们',
		);

		if ( isset( $zh_values[ $name ] ) ) {
			return $zh_values[ $name ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'zh-tw' === pll_current_language( 'slug' ) ) {
		$zhtw_values = array(
			'cal_footer_cta_title'         => '成為專業廚師',
			'cal_footer_gallery_heading'   => '圖庫',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy 為廚師、糕點師和餐廳經理提供實踐型專業培訓。請預約我們的顧問以獲取更多資訊。',
			'cal_footer_contact_heading'   => '聯絡我們',
			'cal_footer_address'           => 'The Culina Academy LTD — 註冊地址：123 Example Street, Your City',
			'cal_footer_policies_heading'  => '我們的政策',
			'cal_footer_bottom_line'       => 'The Culina Academy 在Your City提供專業廚師培訓，透過高水準廚房中的實作學習培養專業技能。',
			'link_viewourcourses'          => '查看我們的課程',
			'link_termsandconditions'      => '條款與條件',
			'link_privacypolicy'           => '隱私政策',
			'link_complaintspolicyandprocedure' => '投訴政策',
			'link_attendanceandpunctualitypolicy' => '出勤政策',
			'link_cookiespolicy'           => 'Cookie政策',
			'link_blog'                    => '部落格',
			'link_contactus'               => '聯絡我們',
		);

		if ( isset( $zhtw_values[ $name ] ) ) {
			return $zhtw_values[ $name ];
		}
	}

	if ( function_exists( 'pll__' ) ) {
		$translated_value = pll__( $value );

		if ( $translated_value !== $value ) {
			return function_exists( 'cal_decode_pll_escapes' ) ? cal_decode_pll_escapes( $translated_value ) : $translated_value;
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'fr' === pll_current_language( 'slug' ) ) {
		$french_values = array(
			'cal_footer_cta_title'         => 'Devenez chef professionnel',
			'cal_footer_gallery_heading'   => 'Galerie',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy propose une formation professionnelle pratique pour les chefs, les pâtissiers et les directeurs de restaurant. Prenez rendez-vous avec l’un de nos conseillers pour plus d’informations.',
			'cal_footer_contact_heading'   => 'Nous contacter',
			'cal_footer_address'           => 'The Culina Academy LTD — Siège social : 123 Example Street, Your City',
			'cal_footer_policies_heading'  => 'Nos politiques',
			'cal_footer_bottom_line'       => 'The Culina Academy propose une formation de chef professionnel à Your City, avec un apprentissage pratique et concret dans des cuisines de haut niveau.',
			'link_viewourcourses'          => 'Découvrez nos formations',
			'link_termsandconditions'      => 'Conditions générales',
			'link_privacypolicy'           => 'Politique de confidentialité',
			'link_complaintspolicyandprocedure' => 'Politique de réclamations',
			'link_attendanceandpunctualitypolicy' => 'Politique de présence',
			'link_cookiespolicy'           => 'Politique relative aux cookies',
			'link_blog'                    => 'Blog',
			'link_contactus'               => 'Contactez-nous',
		);

		if ( isset( $french_values[ $name ] ) ) {
			return $french_values[ $name ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'it' === pll_current_language( 'slug' ) ) {
		$italian_values = array(
			'cal_footer_cta_title'         => 'Diventa uno chef professionista',
			'cal_footer_gallery_heading'   => 'Galleria',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy offre una formazione professionale pratica per chef, pasticceri e direttori di ristorante. Prenota un appuntamento con uno dei nostri consulenti per maggiori informazioni.',
			'cal_footer_contact_heading'   => 'Contattaci',
			'cal_footer_address'           => 'The Culina Academy LTD — Sede legale: 123 Example Street, Your City',
			'cal_footer_policies_heading'  => 'Le nostre politiche',
			'cal_footer_bottom_line'       => 'The Culina Academy offre una formazione professionale per chef a Your City, con un apprendimento pratico e concreto in cucine di alto livello.',
			'link_viewourcourses'          => 'Scopri i nostri corsi',
			'link_termsandconditions'      => 'Termini e condizioni',
			'link_privacypolicy'           => 'Informativa sulla privacy',
			'link_complaintspolicyandprocedure' => 'Politica e procedura per i reclami',
			'link_attendanceandpunctualitypolicy' => 'Politica di frequenza e puntualità',
			'link_cookiespolicy'           => 'Politica sui cookie',
			'link_blog'                    => 'Blog',
			'link_contactus'               => 'Contattaci',
		);

		if ( isset( $italian_values[ $name ] ) ) {
			return $italian_values[ $name ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'es' === pll_current_language( 'slug' ) ) {
		$spanish_values = array(
			'cal_footer_cta_title'         => 'Conviértete en chef profesional',
			'cal_footer_gallery_heading'   => 'Galería',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy ofrece formación profesional práctica para chefs, pasteleros y directores de restaurante. Reserve una cita con uno de nuestros asesores para obtener más información.',
			'cal_footer_contact_heading'   => 'Contáctanos',
			'cal_footer_address'           => 'The Culina Academy LTD — Domicilio social: 123 Example Street, Your City',
			'cal_footer_policies_heading'  => 'Nuestras políticas',
			'cal_footer_bottom_line'       => 'The Culina Academy ofrece formación profesional para chefs en Your City, con aprendizaje práctico y directo en cocinas de alto nivel.',
			'link_viewourcourses'          => 'Ver nuestros cursos',
			'link_termsandconditions'      => 'Términos y condiciones',
			'link_privacypolicy'           => 'Política de privacidad',
			'link_complaintspolicyandprocedure' => 'Política y procedimiento de reclamaciones',
			'link_attendanceandpunctualitypolicy' => 'Política de asistencia y puntualidad',
			'link_cookiespolicy'           => 'Política de cookies',
			'link_blog'                    => 'Blog',
			'link_contactus'               => 'Contáctanos',
		);

		if ( isset( $spanish_values[ $name ] ) ) {
			return $spanish_values[ $name ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'zh' === pll_current_language( 'slug' ) ) {
		$zh_values = array(
			'cal_footer_cta_title'         => '成为专业厨师',
			'cal_footer_gallery_heading'   => '图库',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy 为厨师、糕点师和餐厅经理提供实践型专业培训。请预约我们的顾问以获取更多信息。',
			'cal_footer_contact_heading'   => '联系我们',
			'cal_footer_address'           => 'The Culina Academy LTD — 注册地址：123 Example Street, Your City',
			'cal_footer_policies_heading'  => '我们的政策',
			'cal_footer_bottom_line'       => 'The Culina Academy 在Your City提供专业厨师培训，通过高水准厨房中的实践学习培养专业技能。',
			'link_viewourcourses'          => '查看我们的课程',
			'link_termsandconditions'      => '条款和条件',
			'link_privacypolicy'           => '隐私政策',
			'link_complaintspolicyandprocedure' => '投诉政策',
			'link_attendanceandpunctualitypolicy' => '出勤政策',
			'link_cookiespolicy'           => 'Cookie政策',
			'link_blog'                    => '博客',
			'link_contactus'               => '联系我们',
		);

		if ( isset( $zh_values[ $name ] ) ) {
			return $zh_values[ $name ];
		}
	}

	if ( function_exists( 'pll_current_language' ) && 'zh-tw' === pll_current_language( 'slug' ) ) {
		$zhtw_values = array(
			'cal_footer_cta_title'         => '成為專業廚師',
			'cal_footer_gallery_heading'   => '圖庫',
			'cal_footer_about_heading'     => 'The Culina Academy',
			'cal_footer_about'             => 'Culina Academy 為廚師、糕點師和餐廳經理提供實踐型專業培訓。請預約我們的顧問以獲取更多資訊。',
			'cal_footer_contact_heading'   => '聯絡我們',
			'cal_footer_address'           => 'The Culina Academy LTD — 註冊地址：123 Example Street, Your City',
			'cal_footer_policies_heading'  => '我們的政策',
			'cal_footer_bottom_line'       => 'The Culina Academy 在Your City提供專業廚師培訓，透過高水準廚房中的實作學習培養專業技能。',
			'link_viewourcourses'          => '查看我們的課程',
			'link_termsandconditions'      => '條款與條件',
			'link_privacypolicy'           => '隱私政策',
			'link_complaintspolicyandprocedure' => '投訴政策',
			'link_attendanceandpunctualitypolicy' => '出勤政策',
			'link_cookiespolicy'           => 'Cookie政策',
			'link_blog'                    => '部落格',
			'link_contactus'               => '聯絡我們',
		);

		if ( isset( $zhtw_values[ $name ] ) ) {
			return $zhtw_values[ $name ];
		}
	}

	$context = 'Culina Academy Header Footer';

	if ( has_filter( 'wpml_translate_single_string' ) ) {
		$value = apply_filters( 'wpml_translate_single_string', $value, $context, $name );
	}

	if ( function_exists( 'pll__' ) ) {
		$value = pll__( $value );
	}

	return function_exists( 'cal_decode_pll_escapes' ) ? cal_decode_pll_escapes( $value ) : $value;
}

add_action( 'init', 'cal_register_header_footer_translation_strings', 20 );
function cal_register_header_footer_translation_strings() {
	$string_fields = array(
		'cal_header_phone',
		'cal_header_email',
		'cal_footer_cta_title',
		'cal_footer_gallery_heading',
		'cal_footer_about_heading',
		'cal_footer_about',
		'cal_footer_contact_heading',
		'cal_footer_address',
		'cal_footer_phone',
		'cal_footer_email',
		'cal_footer_policies_heading',
		'cal_footer_bottom_line',
	);

	foreach ( $string_fields as $field_name ) {
		$value = get_option( 'options_' . $field_name );
		cal_register_header_footer_string( $field_name, $value );
	}

	$link_fields = array(
		'cal_footer_cta_link',
	);

	foreach ( $link_fields as $field_name ) {
		$link = get_option( 'options_' . $field_name );
		if ( is_array( $link ) && ! empty( $link['title'] ) ) {
			cal_register_header_footer_string( $field_name . '_title', $link['title'] );
		}
	}

	$policy_count = (int) get_option( 'options_cal_footer_policy_links', 0 );
	for ( $i = 0; $i < $policy_count; $i++ ) {
		$link = get_option( 'options_cal_footer_policy_links_' . $i . '_link' );
		if ( is_array( $link ) && ! empty( $link['title'] ) ) {
			cal_register_header_footer_string( 'cal_footer_policy_links_' . $i . '_title', $link['title'] );
		}
	}

	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: array() as $item ) {
			if ( 'custom' === $item->type && ! empty( $item->title ) ) {
				cal_register_header_footer_string( 'menu_item_' . $item->ID, $item->title );
			}
		}
	}
}

function cal_register_header_footer_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return;
	}

	$context = 'Culina Academy Header Footer';

	if ( has_action( 'wpml_register_single_string' ) ) {
		do_action( 'wpml_register_single_string', $context, $name, $value );
	}

	if ( function_exists( 'pll_register_string' ) ) {
		pll_register_string( $name, $value, $context );
	}
}

/**
 * Build the footer map URL from the editable address field.
 */
function cal_footer_map_url( $address ) {
	$address = trim( wp_strip_all_tags( (string) $address ) );

	if ( '' === $address ) {
		return 'https://www.google.com/maps';
	}

	return 'https://www.google.com/maps?q=' . rawurlencode( $address );
}

/**
 * Normalize ACF image values.
 */
function cal_image_url( $image ) {
	$attachment_id = cal_image_attachment_id( $image );
	if ( $attachment_id ) {
		$url = cal_localized_attachment_url( $attachment_id );
		if ( $url ) {
			return $url;
		}
	}

	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		return $image['url'];
	}

	if ( is_numeric( $image ) ) {
		$url = wp_get_attachment_image_url( (int) $image, 'full' );
		return $url ? $url : '';
	}

	if ( is_string( $image ) && '' !== $image ) {
		return $image;
	}

	return '';
}

/**
 * Extract an attachment ID from common ACF image return formats.
 *
 * @param mixed $image Image field value.
 * @return int
 */
function cal_image_attachment_id( $image ) {
	if ( is_numeric( $image ) ) {
		return (int) $image;
	}

	if ( is_array( $image ) ) {
		if ( ! empty( $image['ID'] ) ) {
			return (int) $image['ID'];
		}

		if ( ! empty( $image['id'] ) ) {
			return (int) $image['id'];
		}
	}

	if ( is_string( $image ) && '' !== $image ) {
		return (int) attachment_url_to_postid( $image );
	}

	return 0;
}

/**
 * Resolve an attachment URL in the active language when media is translated.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @return string
 */
function cal_localized_attachment_url( $attachment_id, $size = 'full' ) {
	$attachment_id = (int) $attachment_id;
	if ( ! $attachment_id ) {
		return '';
	}

	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
		$translated_id = (int) pll_get_post( $attachment_id, pll_current_language( 'slug' ) );
		if ( $translated_id ) {
			$attachment_id = $translated_id;
		}
	}

	$url = wp_get_attachment_image_url( $attachment_id, $size );

	return $url ? $url : '';
}

/**
 * Render a link array.
 */
function cal_render_link( $link, $class = '' ) {
	if ( empty( $link ) || empty( $link['url'] ) ) {
		return;
	}

	$url    = apply_filters( 'culina_academy_migration_url', $link['url'] );
	$title  = ! empty( $link['title'] ) ? $link['title'] : $url;
	$title  = cal_translate_header_footer_string( 'link_' . sanitize_key( $title ), $title );
	$target = ! empty( $link['target'] ) ? $link['target'] : '';
	?>
	<a href="<?php echo esc_url( $url ); ?>"<?php echo $class ? ' class="' . esc_attr( $class ) . '"' : ''; ?><?php echo $target ? ' target="' . esc_attr( $target ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $title ); ?></a>
	<?php
}

/**
 * Translate a nav menu URL to the current language.
 */
function cal_menu_url_removed( $url, $lang ) {
	return $url;
}

function cal_menu_url_removed_legacy_block() {

	$parsed = wp_parse_url( $url );
	if ( empty( $parsed['path'] ) ) {
		return $url;
	}

	// Handle root URL (e.g. http://q.local/) — return language-specific home URL
	$trimmed = trim( $parsed['path'], '/' );
	if ( ! $trimmed ) {
		return $url;
	}
	$segments = explode( '/', $trimmed );
	$slug = $segments ? $segments[ count( $segments ) - 1 ] : '';
	if ( ! $slug ) {
		return $url;
	}

	// Look up post by slug (exclude nav_menu_items)
	global $wpdb;
	$post_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_status = 'publish' AND post_type != 'nav_menu_item' ORDER BY ID ASC LIMIT 1",
		$slug
	) );

	if ( $post_id ) {
		return $url;
	}

	return $url;
}

/* English source cleanup: menu URLs are now rendered directly from WordPress. */

/**
 * Render the WordPress primary menu using the custom header classes.
 */
function cal_render_primary_wp_menu() {
	$current_language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	$default_language = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
	$use_source_menu  = $current_language && $default_language && $current_language !== $default_language;

	$args = array(
		'container'      => false,
		'menu_class'     => 'crisp-nav-list',
		'fallback_cb'    => false,
		'depth'          => 2,
		'walker'         => new Cal_Crisp_Nav_Walker(),
		'echo'           => false,
		'theme_location' => 'primary',
	);

	if ( ! $use_source_menu ) {
		$menu_html = wp_nav_menu( $args );
		if ( ! empty( $menu_html ) ) {
			echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_nav_menu().
			return;
		}
	}

	$source_menu = cal_get_source_primary_menu();
	if ( ! $source_menu ) {
		return;
	}

	$target_language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	$original_language = function_exists( 'PLL' ) ? PLL()->curlang : null;
	$args['menu'] = $source_menu->term_id;
	unset( $args['theme_location'] );

	if ( function_exists( 'PLL' ) ) {
		$source_language_slug = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
		$source_language      = $source_language_slug ? PLL()->model->get_language( $source_language_slug ) : null;
		if ( $source_language ) {
			$GLOBALS['cal_menu_target_language'] = $target_language;
			PLL()->curlang = $source_language;
		}
	}

	$menu_html = wp_nav_menu( $args );

	if ( function_exists( 'PLL' ) && $original_language ) {
		PLL()->curlang = $original_language;
	}
	unset( $GLOBALS['cal_menu_target_language'] );

	echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_nav_menu().
}

/**
 * Fetch the source primary menu without Polylang substituting a translated term.
 *
 * @return WP_Term|null
 */
function cal_get_source_primary_menu() {
	global $wpdb;

	$term_id = (int) $wpdb->get_var(
		"SELECT t.term_id
		FROM {$wpdb->terms} t
		INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
		WHERE tt.taxonomy = 'nav_menu'
			AND t.name = 'Primary Menu'
		ORDER BY t.term_id ASC
		LIMIT 1"
	);

	$menu = $term_id ? get_term( $term_id, 'nav_menu' ) : null;

	return $menu instanceof WP_Term ? $menu : null;
}

/**
 * Keep the custom header markup while letting WordPress own menu data.
 */
class Cal_Crisp_Nav_Walker extends Walker_Nav_Menu {
	private $hidden_items = array();

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === (int) $depth ) {
			$output .= '<div class="crisp-nav-dropdown">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === (int) $depth ) {
			$output .= '</div>';
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$resolved = cal_resolve_menu_item_for_language( $item );
		if ( empty( $resolved ) ) {
			$this->hidden_items[ $item->ID ] = true;
			return;
		}

		$title = $resolved['title'];
		$url   = $resolved['url'];
		$is_active = cal_is_resolved_menu_item_active( $resolved );

		$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';

		if ( 0 === (int) $depth ) {
			$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
			$item_classes = 'crisp-nav-item' . ( $is_active ? ' current-menu-item' : '' );
			$link_classes = 'crisp-nav-link' . ( $is_active ? ' active' : '' );
			$current_attr = $is_active ? ' aria-current="page"' : '';
			$output      .= '<li class="' . esc_attr( $item_classes ) . '">';
			$output      .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $link_classes ) . '"' . $current_attr . $target . '>';
			$output      .= esc_html( $title );

			if ( $has_children ) {
				$output .= '<span class="crisp-nav-dropdown-icon">' . cal_dropdown_icon() . '</span>';
			}

			$output .= '</a>';
			return;
		}

		$dropdown_classes = 'crisp-nav-dropdown-item' . ( $is_active ? ' active current-menu-item' : '' );
		$current_attr     = $is_active ? ' aria-current="page"' : '';
		$output .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $dropdown_classes ) . '"' . $current_attr . $target . '>' . esc_html( $title ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( ! empty( $this->hidden_items[ $item->ID ] ) ) {
			unset( $this->hidden_items[ $item->ID ] );
			return;
		}

		if ( 0 === (int) $depth ) {
			$output .= '</li>';
		}
	}
}

/**
 * Resolve a menu item to linked content in the active language.
 *
 * @param WP_Post $item Menu item.
 * @return array{title:string,url:string}|array{}
 */
function cal_resolve_menu_item_for_language( $item ) {
	$title = apply_filters( 'the_title', $item->title, $item->ID );
	$url   = ! empty( $item->url ) ? $item->url : '#';
	$lang  = isset( $GLOBALS['cal_menu_target_language'] )
		? sanitize_key( $GLOBALS['cal_menu_target_language'] )
		: ( function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '' );
	$object_id = ! empty( $item->object_id ) && in_array( $item->object, array( 'page', 'ca_course' ), true )
		? (int) $item->object_id
		: cal_internal_menu_item_post_id( $url );
	$source_object_id = $object_id;

	if ( $object_id ) {
		if ( $lang && function_exists( 'pll_get_post' ) ) {
			$translated_id = (int) pll_get_post( $object_id, $lang );
			if ( $translated_id ) {
				$object_id = $translated_id;
			}
		}

		$title = get_the_title( $object_id );
		$url   = get_permalink( $object_id );

		if ( empty( $translated_id ) && $lang ) {
			$title = cal_menu_fallback_title_for_language( $item->title, $lang );
			$url   = cal_menu_fallback_url_for_language( $object_id, $lang, $url );
		}

		$front_id = (int) get_option( 'page_on_front' );
		$source_front_id = $front_id;
		if ( $lang && function_exists( 'pll_get_post' ) ) {
			$front_id = (int) pll_get_post( $front_id, $lang );
		}

		if ( 'page' === get_post_type( $object_id ) && ( $front_id === $object_id || $source_front_id === $source_object_id ) && function_exists( 'pll_home_url' ) ) {
			$title = cal_menu_fallback_title_for_language( 'Home', $lang );
			$url = pll_home_url( $lang );
		} elseif ( $lang ) {
			// Menu labels belong to navigation, not translated page titles.
			// Use the curated label map consistently when a translated page exists.
			$title = cal_menu_fallback_title_for_language( $item->title, $lang );
		}
	} else {
		$title = $lang ? cal_menu_fallback_title_for_language( $title, $lang ) : cal_i18n_text( $title );
		$url   = apply_filters( 'culina_academy_migration_url', $url );
	}

	if ( '' === trim( (string) $title ) || empty( $url ) ) {
		return array();
	}

	return array(
		'title' => $title,
		'url'   => $url,
		'object_id' => $object_id,
	);
}

/**
 * Translate source menu labels when the target page has not been created yet.
 *
 * @param string $title Source title.
 * @param string $lang  Target language.
 * @return string
 */
function cal_menu_fallback_title_for_language( $title, $lang ) {
	$translations = array(
		'fr' => array(
			'Home' => 'Accueil',
			'About Us' => 'À propos de nous',
			'Careers' => 'Carrières',
			'Hospitality Development' => 'Développement hôtelier',
			'How to Apply' => 'Comment postuler',
			'FAQs' => 'FAQ',
			'Online Courses' => 'Cours en ligne',
			'Cooking Courses' => 'Cours de cuisine',
			'International Students' => 'Étudiants internationaux',
			'Restaurant Partners' => 'Restaurants partenaires',
			'Contact Us' => 'Nous contacter',
			'Introduction to the Professional Chef Online Course' => 'Cours en ligne d’initiation au métier de chef professionnel',
			'Advanced Techniques Online Course' => 'Cours en ligne de techniques avancées',
			'Advanced Professional Chef Course' => 'Formation avancée de chef professionnel',
			'Advanced Chef Course' => 'Formation de chef avancée',
			'Professional Chef Course' => 'Formation de chef professionnel',
			'Kosher Professional Chef Course' => 'Formation de chef professionnel casher',
			'Halal Advanced Professional Chef Course' => 'Formation avancée de chef professionnel halal',
			'Professional Chef Course Italian Cuisine' => 'Formation de chef professionnel en cuisine italienne',
			'Professional Patisserie and Confectionery Course' => 'Formation professionnelle en pâtisserie et confiserie',
			'Introduction to the Professional Chef Course' => 'Initiation au métier de chef professionnel',
			'Advanced Cooking Techniques' => 'Techniques culinaires avancées',
			'Intensive Professional Pastry Programme' => 'Programme intensif de pâtisserie professionnelle',
			'Restaurant Management and Food Science' => 'Gestion de restaurant et sciences alimentaires',
			'Vegetarian and Vegan Cooking Course' => 'Formation en cuisine végétarienne et végétalienne',
		),
		'it' => array(
			'Home' => 'Home',
			'About Us' => 'Chi siamo',
			'Careers' => 'Percorsi Professionali',
			'Hospitality Development' => 'Collabora con noi',
			'How to Apply' => 'Ammissioni',
			'FAQs' => 'FAQ',
			'Online Courses' => 'Corsi online',
			'Cooking Courses' => 'Corsi di cucina',
			'International Students' => 'Studenti internazionali',
			'Restaurant Partners' => 'Ristoranti partner',
			'Contact Us' => 'Contattaci',
			'Introduction to the Professional Chef Online Course' => 'Corso Online Introduttivo per Chef Professionista',
			'Advanced Techniques Online Course' => 'Corso Online di Tecniche Avanzate',
			'Advanced Professional Chef Course' => 'Corso Avanzato per Chef Professionista',
			'Advanced Chef Course' => 'Corso Chef Avanzato',
			'Professional Chef Course' => 'Corso per Chef Professionista',
			'Kosher Professional Chef Course' => 'Corso per Chef Professionista Kosher',
			'Halal Advanced Professional Chef Course' => 'Corso Avanzato per Chef Professionista Halal',
			'Professional Chef Course Italian Cuisine' => 'Corso per Chef Professionista in Cucina Italiana',
			'Professional Patisserie and Confectionery Course' => 'Corso Professionale di Pasticceria e Alta Pasticceria',
			'Introduction to the Professional Chef Course' => 'Corso Introduttivo per Chef Professionista',
			'Advanced Cooking Techniques' => 'Tecniche di Cucina Avanzate',
			'Intensive Professional Pastry Programme' => 'Programma Intensivo di Pasticceria Professionale',
			'Restaurant Management and Food Science' => 'Gestione della Ristorazione e Scienze Alimentari',
			'Vegetarian and Vegan Cooking Course' => 'Corso di Cucina Vegetariana e Vegana',
		),
		'es' => array(
			'Home' => 'Inicio',
			'About Us' => 'Quiénes somos',
			'Careers' => 'Carreras',
			'Hospitality Development' => 'Desarrollo hotelero',
			'How to Apply' => 'Cómo postularse',
			'FAQs' => 'Preguntas frecuentes',
			'Online Courses' => 'Cursos online',
			'Cooking Courses' => 'Cursos de cocina',
			'International Students' => 'Estudiantes internacionales',
			'Restaurant Partners' => 'Restaurantes asociados',
			'Contact Us' => 'Contáctanos',
			'Introduction to the Professional Chef Online Course' => 'Curso online de introducción al chef profesional',
			'Advanced Techniques Online Course' => 'Curso en línea de técnicas avanzadas',
			'Advanced Professional Chef Course' => 'Curso avanzado de chef profesional',
			'Advanced Chef Course' => 'Curso de chef avanzado',
			'Professional Chef Course' => 'Curso de chef profesional',
			'Kosher Professional Chef Course' => 'Curso kosher de chef profesional',
			'Halal Advanced Professional Chef Course' => 'Curso avanzado halal de chef profesional',
			'Professional Chef Course Italian Cuisine' => 'Curso de chef profesional de cocina italiana',
			'Professional Patisserie and Confectionery Course' => 'Curso profesional de pastelería y confitería',
			'Introduction to the Professional Chef Course' => 'Introducción al curso de chef profesional',
			'Advanced Cooking Techniques' => 'Técnicas culinarias avanzadas',
			'Intensive Professional Pastry Programme' => 'Programa intensivo de pastelería profesional',
			'Restaurant Management and Food Science' => 'Gestión de restaurantes y ciencia alimentaria',
			'Vegetarian and Vegan Cooking Course' => 'Curso de cocina vegetariana y vegana',
		),
		'zh' => array(
			'Home' => '首页',
			'About Us' => '关于我们',
			'Careers' => '职业发展',
			'Hospitality Development' => '酒店餐饮发展',
			'How to Apply' => '如何申请',
			'FAQs' => '常见问题',
			'Online Courses' => '在线课程',
			'Cooking Courses' => '烹饪课程',
			'International Students' => '国际学生',
			'Restaurant Partners' => '餐厅合作伙伴',
			'Contact Us' => '联系我们',
			'Introduction to the Professional Chef Online Course' => '专业厨师在线课程简介',
			'Advanced Techniques Online Course' => '高级技巧在线课程',
			'Advanced Professional Chef Course' => '高级专业厨师课程',
			'Advanced Chef Course' => '高级厨师课程',
			'Professional Chef Course' => '专业厨师课程',
			'Kosher Professional Chef Course' => '犹太洁食专业厨师课程',
			'Halal Advanced Professional Chef Course' => '清真高级专业厨师课程',
			'Professional Chef Course Italian Cuisine' => '意大利菜专业厨师课程',
			'Professional Patisserie and Confectionery Course' => '专业西点与糖艺课程',
			'Introduction to the Professional Chef Course' => '专业厨师课程简介',
			'Advanced Cooking Techniques' => '高级烹饪技法课程',
			'Intensive Professional Pastry Programme' => '专业西点强化课程',
			'Restaurant Management and Food Science' => '餐厅管理与食品科学课程',
			'Vegetarian and Vegan Cooking Course' => '素食与纯素烹饪课程',
		),
		'zh-tw' => array(
			'Home' => '首頁',
			'About Us' => '關於我們',
			'Careers' => '職涯發展',
			'Hospitality Development' => '餐旅發展',
			'How to Apply' => '如何申請',
			'FAQs' => '常見問題',
			'Online Courses' => '線上課程',
			'Cooking Courses' => '烹飪課程',
			'International Students' => '國際學生',
			'Restaurant Partners' => '餐廳合作夥伴',
			'Contact Us' => '聯絡我們',
			'Introduction to the Professional Chef Online Course' => '專業廚師線上課程簡介',
			'Advanced Techniques Online Course' => '高級技巧線上課程',
			'Advanced Professional Chef Course' => '高級專業廚師課程',
			'Advanced Chef Course' => '高級廚師課程',
			'Professional Chef Course' => '專業廚師課程',
			'Kosher Professional Chef Course' => '猶太潔食專業廚師課程',
			'Halal Advanced Professional Chef Course' => '清真高級專業廚師課程',
			'Professional Chef Course Italian Cuisine' => '義大利菜專業廚師課程',
			'Professional Patisserie and Confectionery Course' => '專業西點與糖藝課程',
			'Introduction to the Professional Chef Course' => '專業廚師課程簡介',
			'Advanced Cooking Techniques' => '高級烹飪技法課程',
			'Intensive Professional Pastry Programme' => '專業西點強化課程',
			'Restaurant Management and Food Science' => '餐廳管理與食品科學課程',
			'Vegetarian and Vegan Cooking Course' => '素食與純素烹飪課程',
		),
	);

	return $translations[ $lang ][ $title ] ?? $title;
}

/**
 * Keep missing translated menu pages visible without creating broken language URLs.
 *
 * @param int    $source_id  English/source post ID.
 * @param string $lang       Target language.
 * @param string $source_url Source URL.
 * @return string
 */
function cal_menu_fallback_url_for_language( $source_id, $lang, $source_url ) {
	if ( ! $source_id || ! $lang || 'en' === $lang ) {
		return $source_url;
	}

	$post = get_post( $source_id );
	if ( ! $post || 'page' !== $post->post_type ) {
		return $source_url;
	}

	$slug = $post->post_name;
	if ( 'home' === $slug || (int) get_option( 'page_on_front' ) === (int) $source_id ) {
		return function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' . $lang . '/' );
	}

	return home_url( '/' . rawurlencode( $slug ) . '/' );
}

/**
 * Determine whether a resolved translated menu item points to the current page.
 *
 * @param array $resolved Resolved menu item data.
 * @return bool
 */
function cal_is_resolved_menu_item_active( $resolved ) {
	$current_id = (int) get_queried_object_id();
	$object_id  = ! empty( $resolved['object_id'] ) ? (int) $resolved['object_id'] : 0;

	if ( $current_id && $object_id && $current_id === $object_id ) {
		return true;
	}

	if ( empty( $resolved['url'] ) ) {
		return false;
	}

	$current_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	$target_path  = wp_parse_url( $resolved['url'], PHP_URL_PATH );

	$current_path = cal_normalize_menu_active_path( $current_path );
	$target_path  = cal_normalize_menu_active_path( $target_path );

	return $current_path && $target_path && $current_path === $target_path;
}

/**
 * Normalize URL paths before active menu comparison.
 *
 * @param string $path URL path.
 * @return string
 */
function cal_normalize_menu_active_path( $path ) {
	$path = '/' . trim( (string) $path, '/' );
	$path = preg_replace( '#/+#', '/', $path );

	if ( '/' !== $path ) {
		$path = untrailingslashit( $path );
	}

	return strtolower( $path );
}

/**
 * Resolve a same-site custom menu URL to its source post.
 *
 * @param string $url Menu URL.
 * @return int
 */
function cal_internal_menu_item_post_id( $url ) {
	if ( empty( $url ) ) {
		return 0;
	}

	$site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$url_host  = wp_parse_url( $url, PHP_URL_HOST );
	if ( $url_host && strtolower( $url_host ) !== strtolower( (string) $site_host ) ) {
		return 0;
	}

	$post_id = url_to_postid( $url );
	if ( $post_id ) {
		return (int) $post_id;
	}

	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( preg_match( '#^[a-z]{2}(?:-[a-z]{2})?$#i', $path ) ) {
		return (int) get_option( 'page_on_front' );
	}
	$path = preg_replace( '#^[a-z]{2}(?:-[a-z]{2})?/#i', '', $path );

	if ( '' === $path ) {
		return (int) get_option( 'page_on_front' );
	}

	$segments = explode( '/', $path );
	if ( count( $segments ) >= 2 && 'course' === $segments[0] ) {
		return cal_get_post_id_by_slug( sanitize_title( $segments[1] ), 'ca_course' );
	}

	if ( 1 === count( $segments ) ) {
		return cal_get_post_id_by_slug( sanitize_title( $segments[0] ), 'page' );
	}

	return 0;
}

/**
 * Return a high-resolution SVG flag URL for a given language code.
 * Uses flag-icons CDN (https://flagicons.lipis.dev/) — 4×3 ratio SVGs.
 * Falls back to the provided flag URL if the code is unknown.
 *
 * @param string $lang_code  Language code.
 * @param string $source_url Original Polylang flag URL.
 * @return string
 */
function cal_hd_flag_url( $lang_code, $source_url = '' ) {
	// Map Polylang language slugs → ISO 3166-1 alpha-2 country codes
	$map = array(
		'en' => 'gb',   // English → Great Britain
		'fr' => 'fr',   // French  → France
		'it' => 'it',   // Italian → Italy
		'es' => 'es',   // Spanish → Spain
		'de' => 'de',
		'pt' => 'pt',
		'nl' => 'nl',
		'ru' => 'ru',
		'zh' => 'cn',   // Simplified Chinese → China
		'zh-tw' => 'tw', // Traditional Chinese → Taiwan
		'ar' => 'sa',
		'pl' => 'pl',
		'sv' => 'se',
		'da' => 'dk',
		'fi' => 'fi',
		'nb' => 'no',
		'tr' => 'tr',
		'ko' => 'kr',
	);

	$code = strtolower( trim( $lang_code ) );
	$iso  = $map[ $code ] ?? $code;

	if ( ! preg_match( '/^[a-z]{2}$/', $iso ) ) {
		return $source_url;
	}

	$relative_path = '/assets/images/flags/' . $iso . '.svg';
	$absolute_path = get_template_directory() . $relative_path;

	return file_exists( $absolute_path ) ? get_template_directory_uri() . $relative_path : $source_url;
}

/**
 * Render language links.
 */
function cal_render_language_links( $languages, $class, $use_list = true ) {
	if ( empty( $languages ) ) {
		return '';
	}

	$tag       = $use_list ? 'ul' : 'div';
	$item_open = $use_list ? '<li>' : '';
	$item_end  = $use_list ? '</li>' : '';
	$output    = '';

	$cal_source_id = cal_get_current_translation_source_id();
	$output .= '<' . esc_attr( $tag ) . ' class="' . esc_attr( $class ) . '">';
	foreach ( $languages as $language ) {
		$flag    = cal_hd_flag_url( $language['code'] ?? '', cal_image_url( $language['flag'] ?? '' ) );
		$url     = $language['url'] ?? '';
		$label   = $language['label'] ?? '';
		$code    = $language['code'] ?? '';
		$current = ! empty( $language['current'] );

		if ( ! $flag || ! $url ) {
			continue;
		}

		$active_attr = $current ? ' class="active" aria-current="page"' : '';

		$output .= $item_open;
		$output .= '<a href="' . esc_url( $url ) . '"' . ( $code ? ' hreflang="' . esc_attr( $code ) . '" lang="' . esc_attr( $code ) . '"' : '' ) . $active_attr . ' aria-label="' . esc_attr( $label ) . '">';
		$output .= '<img src="' . esc_url( $flag ) . '" alt="' . esc_attr( $label ) . '" width="40" height="30" loading="lazy">';
		$output .= '</a>';
		$output .= $item_end;
	}
	$output .= '</' . esc_attr( $tag ) . '>';

	return $output;
}

function cal_get_language_output() {
	return cal_render_language_links( cal_get_language_links(), 'crisp-nav-lang-switcher' );
}

function cal_get_language_links() {
	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages(
			array(
				'raw'                    => 1,
				'hide_if_empty'          => 0,
				'hide_if_no_translation' => 0,
			)
		);

		if ( ! empty( $languages ) && is_array( $languages ) ) {
			return array_map(
				function ( $language ) {
					return array(
						'code'        => $language['slug'] ?? '',
						'label'       => $language['name'] ?? '',
						'flag'        => $language['flag'] ?? '',
						'url'         => cal_normalize_language_switcher_url( $language['url'] ?? '', $language['slug'] ?? '' ),
						'current'     => ! empty( $language['current_lang'] ),
					);
				},
				$languages
			);
		}
	}

	return array();
}

function cal_normalize_language_switcher_url( $url, $lang ) {
	if ( ! $url || ! $lang ) {
		return $url;
	}

	$current_id     = cal_get_current_translation_source_id();
	$translation_id = $current_id ? cal_get_linked_post_translation_id( $current_id, $lang ) : 0;

	if ( $translation_id ) {
		if ( 'page' === get_post_type( $translation_id ) && (int) get_option( 'page_on_front' ) === (int) $translation_id ) {
			return home_url( '/' . trim( $lang, '/' ) . '/' );
		}

		return cal_build_language_post_url( $translation_id, $lang );
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	$path = '/' . ltrim( (string) $path, '/' );
	$path = preg_replace( '#/+#', '/', $path );

	return $url;
}

function cal_get_current_translation_source_id() {
	$current_id = get_queried_object_id();
	if ( $current_id ) {
		return (int) $current_id;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = wp_parse_url( $path, PHP_URL_PATH );
	$path = '/' . trim( (string) $path, '/' ) . '/';
	$path = preg_replace( '#/+#', '/', $path );

	if ( preg_match( '#^/([a-z]{2}(?:-[a-z]{2})?)/?$#i', $path ) ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id && function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
			$translated_front_id = pll_get_post( $front_id, pll_current_language( 'slug' ) );
			return $translated_front_id ? (int) $translated_front_id : $front_id;
		}

		return $front_id;
	}

	$path_without_lang = preg_replace( '#^/[a-z]{2}(?:-[a-z]{2})?/#i', '/', $path );

	if ( preg_match( '#^/course/([^/]+)/$#', $path_without_lang, $matches ) ) {
		return cal_get_post_id_by_slug( sanitize_title( $matches[1] ), 'ca_course' );
	}

	$slug = trim( $path_without_lang, '/' );
	if ( $slug && false === strpos( $slug, '/' ) ) {
		return cal_get_post_id_by_slug( sanitize_title( $slug ), 'page' );
	}

	return 0;
}

function cal_get_post_id_by_slug( $slug, $post_type ) {
	if ( ! $slug || ! $post_type ) {
		return 0;
	}

	global $wpdb;

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID
			FROM {$wpdb->posts}
			WHERE post_name = %s
				AND post_type = %s
				AND post_status = 'publish'
			LIMIT 1",
			$slug,
			$post_type
		)
	);
}

function cal_get_linked_post_translation_id( $post_id, $lang ) {
	$post_id = (int) $post_id;
	$lang    = sanitize_key( $lang );

	if ( ! $post_id || ! $lang ) {
		return 0;
	}

	global $wpdb;

	$description = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT tt.description
			FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			WHERE tr.object_id = %d
				AND tt.taxonomy = 'post_translations'
			LIMIT 1",
			$post_id
		)
	);

	$translations = $description ? maybe_unserialize( $description ) : array();
	if ( is_array( $translations ) && ! empty( $translations[ $lang ] ) ) {
		return (int) $translations[ $lang ];
	}

	return function_exists( 'pll_get_post' ) ? (int) pll_get_post( $post_id, $lang ) : 0;
}

/**
 * Replace internal links in rich text with the matching Polylang translation.
 *
 * External URLs, fragments, files, and links that cannot be mapped to a
 * translated WordPress object are left unchanged.
 *
 * @param string $content Rich-text HTML.
 * @return string
 */
function cal_translate_content_links( $content ) {
	if ( ! is_string( $content ) || '' === trim( $content ) || ! function_exists( 'pll_current_language' ) ) {
		return $content;
	}

	$lang = pll_current_language( 'slug' );
	if ( ! $lang ) {
		return $content;
	}

	return preg_replace_callback(
		'/\bhref=(["\'])(.*?)\1/i',
		static function ( $matches ) use ( $lang ) {
			$url = html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' );
			if ( '' === $url || 0 === strpos( $url, '#' ) || preg_match( '#^(?:mailto:|tel:|javascript:)#i', $url ) ) {
				return $matches[0];
			}

			$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
			$url_host  = wp_parse_url( $url, PHP_URL_HOST );
			if ( $url_host && $home_host && strtolower( $url_host ) !== strtolower( $home_host ) ) {
				return $matches[0];
			}

			$post_id = url_to_postid( $url );
			if ( ! $post_id ) {
				return $matches[0];
			}

			$translated_id = cal_get_linked_post_translation_id( $post_id, $lang );
			if ( ! $translated_id ) {
				return $matches[0];
			}

			$translated_url = cal_build_language_post_url( $translated_id, $lang );
			if ( ! $translated_url ) {
				return $matches[0];
			}

			return 'href=' . $matches[1] . esc_url( $translated_url ) . $matches[1];
		},
		$content
	);
}

function cal_force_language_prefix_url( $url, $lang ) {
	if ( ! $url || ! $lang ) {
		return $url;
	}

	$parts = wp_parse_url( $url );
	$path  = isset( $parts['path'] ) ? '/' . ltrim( $parts['path'], '/' ) : '/';
	$path  = preg_replace( '#/+#', '/', $path );
	$lang  = trim( (string) $lang, '/' );

	if ( 0 === strpos( $path, '/' . $lang . '/' ) ) {
		return $url;
	}

	$path = preg_replace( '#^/[a-z]{2}(?:-[a-z]{2})?/#i', '/', $path );

	return home_url( '/' . $lang . '/' . ltrim( $path, '/' ) );
}

function cal_build_language_post_url( $post_id, $lang ) {
	$post_id = (int) $post_id;
	$lang    = trim( (string) $lang, '/' );

	if ( ! $post_id || ! $lang ) {
		return '';
	}

	$post_type = get_post_type( $post_id );
	$slug      = get_post_field( 'post_name', $post_id );

	if ( 'page' === $post_type ) {
		if ( (int) get_option( 'page_on_front' ) === $post_id || 'home' === $slug ) {
			return home_url( '/' . $lang . '/' );
		}

		return home_url( '/' . $lang . '/' . trim( $slug, '/' ) . '/' );
	}

	if ( 'ca_course' === $post_type ) {
		return home_url( '/' . $lang . '/course/' . trim( $slug, '/' ) . '/' );
	}

	return cal_force_language_prefix_url( get_permalink( $post_id ), $lang );
}

function cal_phone_icon() {
	return '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>';
}

function cal_email_icon() {
	return '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>';
}

function cal_dropdown_icon() {
	return '<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg>';
}

function cal_menu_icon() {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
}

function cal_close_icon() {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
}
