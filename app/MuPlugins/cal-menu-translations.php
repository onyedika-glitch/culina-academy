<?php
/**
 * Plugin Name: Culina Academy Menu Translations
 * Description: Translates menu items and breadcrumbs for Polylang
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Menu item translations database
$cal_menu_translations = array(
	'en' => array(
		'Home' => 'Home',
		'About Us' => 'About Us',
		'Online Courses' => 'Online Courses',
		'Cooking Courses' => 'Cooking Courses',
		'International Students' => 'International Students',
		'Restaurant Partners' => 'Restaurant Partners',
		'Reviews' => 'Reviews',
		'Contact' => 'Contact',
		'Gallery' => 'Gallery',
		'Culina Academy' => 'Culina Academy',
		'Contact Us' => 'Contact Us',
		'Our Policies' => 'Our Policies',
		'Careers' => 'Careers',
		'Our School' => 'Our School',
		'How to Apply' => 'How to Apply',
		'FAQs' => 'FAQs',
		'Michelin Stars:' => 'Michelin Stars:',
		'AA Rosettes:' => 'AA Rosettes:',
		'View map' => 'View map',
		'Advanced Professional Chef Course (550 hours)' => 'Advanced Professional Chef Course (550 hours)',
		'Advanced Chef Course (300 Hours)' => 'Advanced Chef Course (300 Hours)',
		'Professional Chef Course (325 Hours)' => 'Professional Chef Course (325 Hours)',
		'Kosher Professional Chef Course (400 Hours)' => 'Kosher Professional Chef Course (400 Hours)',
		'Halal Advanced Professional Chef Course (550 Hours)' => 'Halal Advanced Professional Chef Course (550 Hours)',
		'Professional Chef Course Italian Cuisine (325 hours)' => 'Professional Chef Course Italian Cuisine (325 hours)',
		'Professional Patisserie and Confectionery Course (840 Hours)' => 'Professional Patisserie and Confectionery Course (840 Hours)',
		'Introduction to the Professional Chef Course (80 hours)' => 'Introduction to the Professional Chef Course (80 hours)',
		'Advanced Cooking Techniques (80 hours)' => 'Advanced Cooking Techniques (80 hours)',
		'Intensive Professional Pastry Programme (80 Hours)' => 'Intensive Professional Pastry Programme (80 Hours)',
		'Restaurant Management and Food Science (600 hours)' => 'Restaurant Management and Food Science (600 hours)',
		'Vegetarian and Vegan Cooking Course (80 Hours)' => 'Vegetarian and Vegan Cooking Course (80 Hours)',
	),
	'es' => array(
		'Home' => 'Inicio',
		'About Us' => 'Sobre Nosotros',
		'Online Courses' => 'Cursos En Línea',
		'Cooking Courses' => 'Cursos de Cocina',
		'International Students' => 'Estudiantes Internacionales',
		'Restaurant Partners' => 'Socios Restaurantes',
		'Reviews' => 'Reseñas',
		'Contact' => 'Contacto',
		'Gallery' => 'Galería',
		'Culina Academy' => 'Culina Academy',
		'Contact Us' => 'Contáctenos',
		'Our Policies' => 'Nuestras Políticas',
		'Careers' => 'Carreras',
		'Our School' => 'Nuestra Escuela',
		'How to Apply' => 'Cómo Solicitar',
		'FAQs' => 'Preguntas Frecuentes',
		'Michelin Stars:' => 'Estrellas Michelin:',
		'AA Rosettes:' => 'Rosettes AA:',
		'View map' => 'Ver mapa',
		'Advanced Professional Chef Course (550 hours)' => 'Curso Avanzado de Chef Profesional (550 horas)',
		'Advanced Chef Course (300 Hours)' => 'Curso de Chef Avanzado (300 horas)',
		'Professional Chef Course (325 Hours)' => 'Curso de Chef Profesional (325 horas)',
		'Kosher Professional Chef Course (400 Hours)' => 'Curso de Chef Profesional Kosher (400 horas)',
		'Halal Advanced Professional Chef Course (550 Hours)' => 'Curso Avanzado de Chef Profesional Halal (550 horas)',
		'Professional Chef Course Italian Cuisine (325 hours)' => 'Curso de Chef Profesional de Cocina Italiana (325 horas)',
		'Professional Patisserie and Confectionery Course (840 Hours)' => 'Curso Profesional de Pastelería y Confitería (840 horas)',
		'Introduction to the Professional Chef Course (80 hours)' => 'Introducción al Curso de Chef Profesional (80 horas)',
		'Advanced Cooking Techniques (80 hours)' => 'Técnicas de Cocina Avanzadas (80 horas)',
		'Intensive Professional Pastry Programme (80 Hours)' => 'Programa Intensivo de Pastelería Profesional (80 horas)',
		'Restaurant Management and Food Science (600 hours)' => 'Gestión de Restaurantes y Ciencia Alimentaria (600 horas)',
		'Vegetarian and Vegan Cooking Course (80 Hours)' => 'Curso de Cocina Vegetariana y Vegana (80 horas)',
	),
	'fr' => array(
		'Home' => 'Accueil',
		'About Us' => 'À propos de nous',
		'Online Courses' => 'Cours en Ligne',
		'Cooking Courses' => 'Cours de Cuisine',
		'International Students' => 'Étudiants Internationaux',
		'Restaurant Partners' => 'Partenaires Restaurateurs',
		'Reviews' => 'Avis',
		'Contact' => 'Contact',
		'Gallery' => 'Galerie',
		'Culina Academy' => 'Culina Academy',
		'Contact Us' => 'Contactez-nous',
		'Our Policies' => 'Nos Politiques',
		'Careers' => 'Carrières',
		'Our School' => 'Notre École',
		'How to Apply' => 'Comment Postuler',
		'FAQs' => 'FAQ',
		'Michelin Stars:' => 'Étoiles Michelin :',
		'AA Rosettes:' => 'Rosettes AA :',
		'View map' => 'Voir la carte',
		'Advanced Professional Chef Course (550 hours)' => 'Cours Avancé de Chef Professionnel (550 heures)',
		'Advanced Chef Course (300 Hours)' => 'Cours de Chef Avancé (300 heures)',
		'Professional Chef Course (325 Hours)' => 'Cours de Chef Professionnel (325 heures)',
		'Kosher Professional Chef Course (400 Hours)' => 'Cours de Chef Professionnel Casher (400 heures)',
		'Halal Advanced Professional Chef Course (550 Hours)' => 'Cours Avancé de Chef Professionnel Halal (550 heures)',
		'Professional Chef Course Italian Cuisine (325 hours)' => 'Cours de Chef Professionnel - Cuisine Italienne (325 heures)',
		'Professional Patisserie and Confectionery Course (840 Hours)' => 'Cours Professionnel de Pâtisserie et Confiserie (840 heures)',
		'Introduction to the Professional Chef Course (80 hours)' => 'Introduction au Cours de Chef Professionnel (80 heures)',
		'Advanced Cooking Techniques (80 hours)' => 'Techniques de Cuisine Avancées (80 heures)',
		'Intensive Professional Pastry Programme (80 Hours)' => 'Programme Intensif de Pâtisserie Professionnelle (80 heures)',
		'Restaurant Management and Food Science (600 hours)' => 'Gestion de Restaurant et Sciences Alimentaires (600 heures)',
		'Vegetarian and Vegan Cooking Course (80 Hours)' => 'Cours de Cuisine Végétarienne et Végane (80 heures)',
	),
	'it' => array(
		'Home' => 'Home',
		'About Us' => 'Chi Siamo',
		'Online Courses' => 'Corsi Online',
		'Cooking Courses' => 'Corsi di Cucina',
		'International Students' => 'Studenti Internazionali',
		'Restaurant Partners' => 'Partner Ristoranti',
		'Reviews' => 'Recensioni',
		'Contact' => 'Contatti',
		'Gallery' => 'Galleria',
		'Culina Academy' => 'Culina Academy',
		'Contact Us' => 'Contattaci',
		'Our Policies' => 'Le Nostre Politiche',
		'Careers' => 'Percorsi Professionali',
		'Our School' => 'La Nostra Scuola',
		'How to Apply' => 'Ammissioni',
		'Hospitality Development' => 'Collabora con noi',
		'FAQs' => 'Domande Frequenti',
		'Michelin Stars:' => 'Stelle Michelin:',
		'AA Rosettes:' => 'Rosette AA:',
		'View map' => 'Vedi mappa',
		'Advanced Professional Chef Course (550 hours)' => 'Corso Avanzato per Chef Professionista (550 ore)',
		'Advanced Chef Course (300 Hours)' => 'Corso Chef Avanzato (300 ore)',
		'Professional Chef Course (325 Hours)' => 'Corso per Chef Professionista (325 ore)',
		'Kosher Professional Chef Course (400 Hours)' => 'Corso per Chef Professionista Kosher (400 ore)',
		'Halal Advanced Professional Chef Course (550 Hours)' => 'Corso Avanzato per Chef Professionista Halal (550 ore)',
		'Professional Chef Course Italian Cuisine (325 hours)' => 'Corso per Chef Professionista in Cucina Italiana (325 ore)',
		'Professional Patisserie and Confectionery Course (840 Hours)' => 'Corso Professionale di Pasticceria e Alta Pasticceria (840 ore)',
		'Introduction to the Professional Chef Course (80 hours)' => 'Corso Introduttivo per Chef Professionista (80 ore)',
		'Advanced Cooking Techniques (80 hours)' => 'Tecniche di Cucina Avanzate (80 ore)',
		'Intensive Professional Pastry Programme (80 Hours)' => 'Programma Intensivo di Pasticceria Professionale (80 ore)',
		'Restaurant Management and Food Science (600 hours)' => 'Gestione della Ristorazione e Scienze Alimentari (600 ore)',
		'Vegetarian and Vegan Cooking Course (80 Hours)' => 'Corso di Cucina Vegetariana e Vegana (80 ore)',
	),
	'zh' => array(
		'Home' => '首页',
		'About Us' => '关于我们',
		'Online Courses' => '在线课程',
		'Cooking Courses' => '烹饪课程',
		'International Students' => '国际学生',
		'Restaurant Partners' => '餐厅合作伙伴',
		'Reviews' => '评价',
		'Contact' => '联系',
		'Gallery' => '图库',
		'Culina Academy' => 'Culina Academy',
		'Contact Us' => '联系我们',
		'Our Policies' => '我们的政策',
		'Careers' => '职业发展',
		'Our School' => '我们的学校',
		'How to Apply' => '如何申请',
		'FAQs' => '常见问题',
		'Michelin Stars:' => '米其林星级：',
		'AA Rosettes:' => 'AA 评分等级：',
		'View map' => '查看地图',
		'Advanced Professional Chef Course (550 hours)' => '高级专业厨师课程（550小时）',
		'Advanced Chef Course (300 Hours)' => '高级厨师课程（300小时）',
		'Professional Chef Course (325 Hours)' => '专业厨师课程（325小时）',
		'Kosher Professional Chef Course (400 Hours)' => 'Kosher专业厨师课程（400小时）',
		'Halal Advanced Professional Chef Course (550 Hours)' => '清真高级专业厨师课程（550小时）',
		'Professional Chef Course Italian Cuisine (325 hours)' => '专业厨师课程意大利菜（325小时）',
		'Professional Patisserie and Confectionery Course (840 Hours)' => '专业糕点和糖果课程（840小时）',
		'Introduction to the Professional Chef Course (80 hours)' => '专业厨师入门课程（80小时）',
		'Advanced Cooking Techniques (80 hours)' => '高级烹饪技术（80小时）',
		'Intensive Professional Pastry Programme (80 Hours)' => '强化专业糕点课程（80 小时）',
		'Restaurant Management and Food Science (600 hours)' => '餐厅管理与食品科学（600 小时）',
		'Vegetarian and Vegan Cooking Course (80 Hours)' => '素食和纯素烹饪课程（80 小时）',
	),
	'zh-tw' => array(
		'Home' => '首頁',
		'About Us' => '關於我們',
		'Online Courses' => '線上課程',
		'Cooking Courses' => '烹飪課程',
		'International Students' => '國際學生',
		'Restaurant Partners' => '餐廳合作夥伴',
		'Reviews' => '評價',
		'Contact' => '聯絡',
		'Gallery' => '圖庫',
		'Culina Academy' => 'Culina Academy',
		'Contact Us' => '聯絡我們',
		'Our Policies' => '我們的政策',
		'Careers' => '職業發展',
		'Our School' => '我們的學校',
		'How to Apply' => '如何申請',
		'FAQs' => '常見問題',
		'Michelin Stars:' => '米其林星級：',
		'AA Rosettes:' => 'AA 評分等級：',
		'View map' => '查看地圖',
		'Advanced Professional Chef Course (550 hours)' => '高級專業廚師課程（550小時）',
		'Advanced Chef Course (300 Hours)' => '高級廚師課程（300小時）',
		'Professional Chef Course (325 Hours)' => '專業廚師課程（325小時）',
		'Kosher Professional Chef Course (400 Hours)' => 'Kosher專業廚師課程（400小時）',
		'Halal Advanced Professional Chef Course (550 Hours)' => '清真高級專業廚師課程（550小時）',
		'Professional Chef Course Italian Cuisine (325 hours)' => '專業廚師課程意大利菜（325小時）',
		'Professional Patisserie and Confectionery Course (840 Hours)' => '專業糕點和糖果課程（840小時）',
		'Introduction to the Professional Chef Course (80 hours)' => '專業廚師入門課程（80小時）',
		'Advanced Cooking Techniques (80 hours)' => '高級烹飪技術（80小時）',
		'Intensive Professional Pastry Programme (80 Hours)' => '強化專業糕點課程（80 小時）',
		'Restaurant Management and Food Science (600 hours)' => '餐廳管理與食品科學（600 小時）',
		'Vegetarian and Vegan Cooking Course (80 Hours)' => '素食和純素烹飪課程（80 小時）',
	),
);

// Main translation function
function cal_translate_menu_item( $text ) {
	global $cal_menu_translations;

	if ( ! function_exists( 'pll_current_language' ) ) {
		return $text;
	}

	$current_lang = pll_current_language( 'slug' );

	if ( ! $current_lang || $current_lang === 'en' ) {
		return $text;
	}

	if ( isset( $cal_menu_translations[ $current_lang ][ $text ] ) ) {
		return $cal_menu_translations[ $current_lang ][ $text ];
	}

	return $text;
}

// Filter nav menu items HTML output - This is the most reliable approach
add_filter( 'walker_nav_menu_start_el', function( $item_output, $item, $depth, $args ) {
	global $cal_menu_translations;

	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang && $current_lang !== 'en' && isset( $cal_menu_translations[ $current_lang ] ) ) {
		foreach ( $cal_menu_translations[ $current_lang ] as $en_text => $translated_text ) {
			// Replace in link text and title attributes
			$item_output = str_replace( '>' . $en_text . '<', '>' . $translated_text . '<', $item_output );
			$item_output = str_replace( 'aria-label="' . $en_text . '"', 'aria-label="' . $translated_text . '"', $item_output );
		}
	}

	return $item_output;
}, 10, 4 );

// Register strings for Polylang
add_action( 'init', function() {
	global $cal_menu_translations;

	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( isset( $cal_menu_translations[ 'en' ] ) ) {
		foreach ( $cal_menu_translations[ 'en' ] as $en_text => $en_text_val ) {
			pll_register_string( 'menu-' . sanitize_title( $en_text ), $en_text, 'Culina Academy Menu' );
		}
	}
} );

// Translate breadcrumb "Home" link
add_filter( 'generate_breadcrumb_home_text', function( $text ) {
	return cal_translate_menu_item( $text );
} );

// Filter page title for breadcrumbs
add_filter( 'wp_title_parts', function( $parts ) {
	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang === 'en' ) {
		return $parts;
	}

	foreach ( $parts as &$part ) {
		$part = cal_translate_menu_item( $part );
	}

	return $parts;
} );

// Filter for GeneratePress breadcrumb navigation
add_filter( 'generate_breadcrumb_defaults', function( $defaults ) {
	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang && $current_lang !== 'en' ) {
		$defaults['home_text'] = cal_translate_menu_item( 'Home' );
	}

	return $defaults;
} );

// Filter breadcrumb HTML output
add_filter( 'generate_breadcrumb_output', function( $output ) {
	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang && $current_lang !== 'en' ) {
		global $cal_menu_translations;

		if ( isset( $cal_menu_translations[ $current_lang ] ) ) {
			foreach ( $cal_menu_translations[ $current_lang ] as $en_text => $translated_text ) {
				$output = str_replace( '>' . $en_text . '<', '>' . $translated_text . '<', $output );
			}
		}
	}

	return $output;
} );

// Handle breadcrumb translation in footer
add_filter( 'wp_nav_menu', function( $nav_menu ) {
	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang && $current_lang !== 'en' ) {
		global $cal_menu_translations;

		if ( isset( $cal_menu_translations[ $current_lang ] ) ) {
			foreach ( $cal_menu_translations[ $current_lang ] as $en_text => $translated_text ) {
				$nav_menu = str_replace( '>' . $en_text . '<', '>' . $translated_text . '<', $nav_menu );
			}
		}
	}

	return $nav_menu;
}, 20 );

// Frontend script to ensure all text is translated
add_action( 'wp_enqueue_scripts', function() {
	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang === 'en' ) {
		return;
	}

	global $cal_menu_translations;

	if ( ! isset( $cal_menu_translations[ $current_lang ] ) ) {
		return;
	}

	$translations_json = json_encode( $cal_menu_translations[ $current_lang ] );

	?>
	<script>
		var chefTranslations = <?php echo $translations_json; ?>;

		document.addEventListener('DOMContentLoaded', function() {
			// Translate menu items
			document.querySelectorAll('.main-nav a, .menu a, nav a').forEach(function(element) {
				var text = element.textContent.trim();
				if (chefTranslations[text]) {
					element.textContent = chefTranslations[text];
				}
			});

			// Translate breadcrumbs specifically
			document.querySelectorAll('nav.breadcrumb a, .breadcrumb a, [role="navigation"] a, nav.cal-policy-breadcrumbs a, nav.cal-page-breadcrumbs a, .cal-policy-breadcrumbs a, .cal-page-breadcrumbs a').forEach(function(element) {
				var text = element.textContent.trim();
				if (chefTranslations[text]) {
					element.textContent = chefTranslations[text];
				}
			});

			// Translate footer headings
			document.querySelectorAll('.cal-footer-column h4').forEach(function(element) {
				var text = element.textContent.trim();
				if (chefTranslations[text]) {
					element.textContent = chefTranslations[text];
				}
			});

			// Translate restaurant partner labels
			document.querySelectorAll('.cal-rp-awards-label, .cal-rp-map-link a').forEach(function(element) {
				var text = element.textContent.trim();
				if (chefTranslations[text]) {
					element.textContent = chefTranslations[text];
				}
			});
		});
	</script>
	<?php
} );

// Better approach: Use output buffering to replace text in breadcrumbs at HTML level
add_action( 'wp_footer', function() {
	$current_lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';

	if ( $current_lang === 'en' ) {
		return;
	}

	global $cal_menu_translations;

	if ( ! isset( $cal_menu_translations[ $current_lang ] ) ) {
		return;
	}

	?>
	<script>
		(function() {
			var translations = <?php echo json_encode( $cal_menu_translations[ $current_lang ] ); ?>;

			// Replace Home in breadcrumbs
			if (translations['Home']) {
				var breadcrumbRegex = />\s*Home\s*</g;
				var breadcrumbReplace = '>' + translations['Home'] + '<';

				// Replace in navigation breadcrumb
				var breadcrumbNav = document.querySelector('nav.breadcrumb, nav.cal-policy-breadcrumbs, nav.cal-page-breadcrumbs');
				if (breadcrumbNav) {
					breadcrumbNav.innerHTML = breadcrumbNav.innerHTML.replace(breadcrumbRegex, breadcrumbReplace);
				}

				// Replace in any link with href to home
				document.querySelectorAll('nav.breadcrumb a[href*="http"], nav.breadcrumb a[href^="/"], nav.cal-policy-breadcrumbs a[href*="http"], nav.cal-policy-breadcrumbs a[href^="/"], nav.cal-page-breadcrumbs a[href*="http"], nav.cal-page-breadcrumbs a[href^="/"]').forEach(function(link) {
					if (link.textContent.trim() === 'Home') {
						link.textContent = translations['Home'];
					}
				});
			}
		})();
	</script>
	<?php
} );
?>