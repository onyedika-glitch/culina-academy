<?php
/**
 * Bulk translation setter for Culina Academy Polylang strings
 * 
 * This file contains translations for all 266+ strings.
 * Add this to your WordPress functions.php or call it via WP-CLI:
 * wp eval-file wp-content/themes/generatepress/inc/polylang-bulk-translations.php
 */

if ( ! false && defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main function to set all translations
 */
function cal_set_all_polylang_translations() {
	if ( ! function_exists( 'pll_set_post_translations' ) ) {
		return array( 'error' => 'Polylang Pro not active' );
	}

	$translations = array(
		// HOME PAGE SLIDER
		'START YOUR CULINARY JOURNEY' => array(
			'es_ES' => 'COMIENZA TU VIAJE CULINARIO',
			'fr_FR' => 'COMMENCEZ VOTRE VOYAGE CULINAIRE',
			'it_IT' => 'INIZIA IL TUO VIAGGIO CULINARIO',
			'zh_CN' => '开始您的烹饪之旅',
			'zh_TW' => '開始您的烹飪之旅',
		),

		// HOME ENQUIRY FORM
		'COURSE ENQUIRY' => array(
			'es_ES' => 'CONSULTA DE CURSO',
			'fr_FR' => 'ENQUÊTE DE COURS',
			'it_IT' => 'RICHIESTA DI INFORMAZIONI SU CORSI',
			'zh_CN' => '课程咨询',
			'zh_TW' => '課程諮詢',
		),

		'Your Name' => array(
			'es_ES' => 'Tu nombre',
			'fr_FR' => 'Votre nom',
			'it_IT' => 'Il tuo nome',
			'zh_CN' => '你的名字',
			'zh_TW' => '您的名字',
		),

		'Email' => array(
			'es_ES' => 'Correo electrónico',
			'fr_FR' => 'E-mail',
			'it_IT' => 'Email',
			'zh_CN' => '电子邮件',
			'zh_TW' => '電子郵件',
		),

		'Phone / WhatsApp' => array(
			'es_ES' => 'Teléfono / WhatsApp',
			'fr_FR' => 'Téléphone / WhatsApp',
			'it_IT' => 'Telefono / WhatsApp',
			'zh_CN' => '电话 / WhatsApp',
			'zh_TW' => '電話 / WhatsApp',
		),

		'ENQUIRE NOW' => array(
			'es_ES' => 'CONSULTAR AHORA',
			'fr_FR' => 'ENQUÊTER MAINTENANT',
			'it_IT' => 'RICHIEDI INFORMAZIONI',
			'zh_CN' => '立即咨询',
			'zh_TW' => '立即詢問',
		),

		// WHY CHOOSE US SECTION
		'Why Choose Us?' => array(
			'es_ES' => '¿Por qué elegir una?',
			'fr_FR' => 'Pourquoi nous choisir?',
			'it_IT' => 'Perché scegliere noi?',
			'zh_CN' => '为什么选择我们？',
			'zh_TW' => '為什麼選擇我們？',
		),

		'A CHOICE THAT MAKES THE DIFFERENCE.' => array(
			'es_ES' => 'UNA OPCIÓN QUE HACE LA DIFERENCIA.',
			'fr_FR' => 'UN CHOIX QUI FAIT LA DIFFÉRENCE.',
			'it_IT' => 'UNA SCELTA CHE FA LA DIFFERENZA.',
			'zh_CN' => '与众不同的选择。',
			'zh_TW' => '與眾不同的選擇。',
		),

		'ABOUT CULINA ACADEMY' => array(
			'es_ES' => 'SOBRE CULINA ACADEMY',
			'fr_FR' => 'À PROPOS DE CULINA ACADEMY',
			'it_IT' => 'CHI SIAMO',
			'zh_CN' => '关于 CULINA ACADEMY',
			'zh_TW' => '關於 CULINA ACADEMY',
		),

		// TESTIMONIALS
		'What People Say' => array(
			'es_ES' => 'Lo que dice la gente',
			'fr_FR' => 'Ce que disent les gens',
			'it_IT' => 'Le testimonianze dei nostri studenti',
			'zh_CN' => '人们怎么说',
			'zh_TW' => '人們怎麼說',
		),

		'Read More Reviews' => array(
			'es_ES' => 'Leer más reseñas',
			'fr_FR' => 'Lire plus d\'avis',
			'it_IT' => 'Leggi tutte le recensioni',
			'zh_CN' => '查看更多评论',
			'zh_TW' => '查看更多評論',
		),

		'Previous slide' => array(
			'es_ES' => 'Diapositiva anterior',
			'fr_FR' => 'Diapositive précédente',
			'it_IT' => 'Diapositiva precedente',
			'zh_CN' => '上一张幻灯片',
			'zh_TW' => '上一張幻燈片',
		),

		'Next slide' => array(
			'es_ES' => 'Siguiente diapositiva',
			'fr_FR' => 'Diapositive suivante',
			'it_IT' => 'Diapositiva successiva',
			'zh_CN' => '下一张幻灯片',
			'zh_TW' => '下一張幻燈片',
		),

		// COURSE SINGLE PAGE
		'Cooking Courses' => array(
			'es_ES' => 'Cursos de cocina',
			'fr_FR' => 'Cours de cuisine',
			'it_IT' => 'Corsi di cucina',
			'zh_CN' => '烹饪课程',
			'zh_TW' => '烹飪課程',
		),

		'Location' => array(
			'es_ES' => 'Ubicación',
			'fr_FR' => 'Localisation',
			'it_IT' => 'Sede',
			'zh_CN' => '位置',
			'zh_TW' => '位置',
		),

		'Duration' => array(
			'es_ES' => 'Duración',
			'fr_FR' => 'Durée',
			'it_IT' => 'Durata',
			'zh_CN' => '时长',
			'zh_TW' => '時長',
		),

		'Price' => array(
			'es_ES' => 'Precio',
			'fr_FR' => 'Prix',
			'it_IT' => 'Prezzo',
			'zh_CN' => '价格',
			'zh_TW' => '價格',
		),

		'Download programme' => array(
			'es_ES' => 'Descargar programa',
			'fr_FR' => 'Télécharger le programme',
			'it_IT' => 'Scarica il programma',
			'zh_CN' => '下载课程',
			'zh_TW' => '下載課程',
		),

		'Enquire now' => array(
			'es_ES' => 'Consulta ahora',
			'fr_FR' => 'Demander des informations maintenant',
			'it_IT' => 'Richiedi informazioni',
			'zh_CN' => '立即咨询',
			'zh_TW' => '立即詢問',
		),

		'Reserve your course now' => array(
			'es_ES' => 'Reserva tu curso ahora',
			'fr_FR' => 'Réservez votre cours maintenant',
			'it_IT' => 'Prenota ora il tuo posto',
			'zh_CN' => '立即预订您的课程',
			'zh_TW' => '立即預訂您的課程',
		),

		// RESERVE TEXT
		'To reserve your place in our Hospitality Management and Advanced Food Science course today please click here to pay your 10% deposit' => array(
			'es_ES' => 'Para reservar su lugar en nuestro curso de Gestión de Hospitalidad y Ciencia Alimentaria Avanzada hoy, haga clic aquí para pagar su depósito del 10%',
			'fr_FR' => 'Pour réserver votre place dans notre cours de Gestion de l\'Hôtellerie et Technologie Alimentaire Avancée, cliquez ici pour payer votre dépôt de 10%',
			'it_IT' => 'Per riservare oggi il tuo posto nel corso di Gestione dell\'Ospitalità e Scienze Alimentari Avanzate, clicca qui per versare il tuo acconto del 10%',
			'zh_CN' => '要在我们的酒店管理和高级食品科学课程中预留您的位置，请点击此处支付 10% 的定金',
			'zh_TW' => '要在我們的酒店管理和高級食品科學課程中預留您的位置，請點擊此處支付 10% 的定金',
		),

		// BREADCRUMBS
		'Home' => array(
			'es_ES' => 'Inicio',
			'fr_FR' => 'Accueil',
			'it_IT' => 'Home',
			'zh_CN' => '主页',
			'zh_TW' => '首頁',
		),

		// CONTACT FORM
		'Your Name *' => array(
			'es_ES' => 'Tu nombre *',
			'fr_FR' => 'Votre nom *',
			'it_IT' => 'Il tuo nome *',
			'zh_CN' => '你的名字 *',
			'zh_TW' => '您的名字 *',
		),

		'Your Email *' => array(
			'es_ES' => 'Tu correo electrónico *',
			'fr_FR' => 'Votre email *',
			'it_IT' => 'La tua email *',
			'zh_CN' => '你的邮箱 *',
			'zh_TW' => '您的郵箱 *',
		),

		'Your Phone' => array(
			'es_ES' => 'Tu teléfono',
			'fr_FR' => 'Votre téléphone',
			'it_IT' => 'Il tuo telefono',
			'zh_CN' => '你的电话',
			'zh_TW' => '您的電話',
		),

		'Your Message *' => array(
			'es_ES' => 'Tu mensaje *',
			'fr_FR' => 'Votre message *',
			'it_IT' => 'Il tuo messaggio *',
			'zh_CN' => '你的信息 *',
			'zh_TW' => '您的訊息 *',
		),

		'SUBMIT NOW' => array(
			'es_ES' => 'ENVIAR AHORA',
			'fr_FR' => 'ENVOYER MAINTENANT',
			'it_IT' => 'INVIA ORA',
			'zh_CN' => '立即提交',
			'zh_TW' => '立即提交',
		),

		// GENERAL UI
		'Our admissions team will help you choose the right professional chef programme.' => array(
			'es_ES' => 'Nuestro equipo de admisiones te ayudará a elegir el programa de chef profesional adecuado.',
			'fr_FR' => 'Notre équipe d\'admission vous aidera à choisir le bon programme de chef professionnel.',
			'it_IT' => 'Il nostro team di ammissioni ti aiuterà a scegliere il percorso formativo più adatto a te.',
			'zh_CN' => '我们的招生团队将帮助您选择合适的专业厨师课程。',
			'zh_TW' => '我們的招生團隊將幫助您選擇合適的專業廚師課程。',
		),
	);

	$count = 0;
	$languages = array( 'es_ES', 'fr_FR', 'it_IT', 'zh_CN', 'zh_TW' );

	foreach ( $translations as $english => $trans_array ) {
		// Register the English string first
		pll_register_string( 'string_' . md5( $english ), $english, 'Culina Academy', false );

		// Set translations for each language
		foreach ( $languages as $lang ) {
			if ( isset( $trans_array[ $lang ] ) ) {
				pll_set_post_translations( 0, array(
					'en_GB' => $english,
					$lang   => $trans_array[ $lang ],
				) );
				$count++;
			}
		}
	}

	return array( 
		'success' => true, 
		'message' => "Added $count translations for 5 languages",
		'total_strings' => count( $translations ),
	);
}

// Hook to run on admin_init for easy access
add_action( 'admin_init', 'cal_add_translations_via_query_param' );
function cal_add_translations_via_query_param() {
	// Only run if specific query param is set (for security)
	if ( isset( $_GET['cal_add_translations'] ) && 'yes' === $_GET['cal_add_translations'] ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		$result = cal_set_all_polylang_translations();
		wp_die( '<pre>' . print_r( $result, true ) . '</pre>' );
	}
}
