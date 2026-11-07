<?php
/**
 * Plugin Name: Culina Academy Footer & UI Translations
 * Description: Writes all footer and course UI string translations into Polylang term-meta store.
 *              Visit /wp-admin/?cal_footer_trans=apply to run.
 *              Visit /wp-admin/?cal_footer_trans=status to verify.
 * Version: 4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Master translation map  (English source => per-language translations)
// ─────────────────────────────────────────────────────────────────────────────
function cal_footer_trans_map() {
	return array(

		// ── Footer headings ──────────────────────────────────────────────
		'Gallery' => array( 'fr' => 'Galerie', 'it' => 'Galleria', 'es' => 'Galería', 'zh' => '图库', 'zh-tw' => '圖庫' ),

		// Brand name: keep English, do NOT translate
		'Culina Academy'    => array( 'fr' => 'The Culina Academy', 'it' => 'The Culina Academy', 'es' => 'The Culina Academy', 'zh' => 'The Culina Academy', 'zh-tw' => 'The Culina Academy' ),
		'Culina Academy Your City'   => array( 'fr' => 'The Culina Academy', 'it' => 'The Culina Academy', 'es' => 'The Culina Academy', 'zh' => 'The Culina Academy', 'zh-tw' => 'The Culina Academy' ),
		'Culina Academy Your City'    => array( 'fr' => 'The Culina Academy', 'it' => 'The Culina Academy', 'es' => 'The Culina Academy', 'zh' => 'The Culina Academy', 'zh-tw' => 'The Culina Academy' ),

		'Contact Us'  => array( 'fr' => 'Contactez-nous',      'it' => 'Contattaci',          'es' => 'Contáctenos',       'zh' => '联系我们',   'zh-tw' => '聯絡我們' ),
		'Our Policies'=> array( 'fr' => 'Nos Politiques',       'it' => 'Le Nostre Politiche', 'es' => 'Nuestras Políticas','zh' => '我们的政策', 'zh-tw' => '我們的政策' ),

		// ── CTA title ────────────────────────────────────────────────────
		'BECOME A PROFESSIONAL CHEF'  => array( 'fr' => 'DEVENEZ CHEF PROFESSIONNEL',       'it' => 'DIVENTA UNO CHEF PROFESSIONISTA', 'es' => 'CONVIÉRTETE EN CHEF PROFESIONAL', 'zh' => '成为专业厨师',   'zh-tw' => '成為專業廚師' ),
		'BECAME A PROFESSIONAL CHEF'  => array( 'fr' => 'DEVENEZ CHEF PROFESSIONNEL',       'it' => 'DIVENTA UNO CHEF PROFESSIONISTA', 'es' => 'CONVIÉRTETE EN CHEF PROFESIONAL', 'zh' => '成为专业厨师',   'zh-tw' => '成為專業廚師' ),
		'Become a Professional Chef'  => array( 'fr' => 'Devenez chef professionnel',       'it' => 'Diventa uno chef professionista', 'es' => 'Conviértete en chef profesional', 'zh' => '成为专业厨师',   'zh-tw' => '成為專業廚師' ),
		'Devenu chef professionnel'   => array( 'fr' => 'Devenez chef professionnel',       'it' => 'Diventa uno chef professionista', 'es' => 'Conviértete en chef profesional', 'zh' => '成为专业厨师',   'zh-tw' => '成為專業廚師' ),
		'View our courses'            => array( 'fr' => 'Voir nos cours',                   'it' => 'Vedi i nostri corsi',             'es' => 'Ver nuestros cursos',             'zh' => '查看我们的课程', 'zh-tw' => '查看我們的課程' ),
		'View Our Courses'            => array( 'fr' => 'Voir nos cours',                   'it' => 'Vedi i nostri corsi',             'es' => 'Ver nuestros cursos',             'zh' => '查看我们的课程', 'zh-tw' => '查看我們的課程' ),

		// ── Footer about text ────────────────────────────────────────────
		'Culina Academy offers hands-on professional training for chefs, pastry chefs and restaurant managers. Book an appointment with one of our advisors for more information.' => array(
			'fr'    => "Culina Academy propose une formation professionnelle pratique pour les chefs, les pâtissiers et les directeurs de restaurant. Prenez rendez-vous avec l’un de nos conseillers pour plus d’informations.",
			'it'    => "Culina Academy offre una formazione professionale pratica per chef, pasticceri e direttori di ristorante. Prenota un appuntamento con uno dei nostri consulenti per maggiori informazioni.",
			'es'    => "Culina Academy ofrece formación profesional práctica para chefs, pasteleros y directores de restaurante. Reserve una cita con uno de nuestros asesores para obtener más información.",
			'zh'    => "Culina Academy 为厨师、糕点师和餐厅经理提供实践型专业培训。请预约我们的顾问以获取更多信息。",
			'zh-tw' => "Culina Academy 為廚師、糕點師和餐廳經理提供實踐型專業培訓。請預約我們的顧問以獲取更多資訊。",
		),
		// ── Footer address ───────────────────────────────────────────────
		'The Culina Academy LTD' . "\n" . 'Registered Office: 123 Example Street, Your City' => array(
			'fr'    => "The Culina Academy LTD\nSiège social : 123 Example Street, Your City",
			'it'    => "The Culina Academy LTD\nSede legale: 123 Example Street, Your City",
			'es'    => "The Culina Academy LTD\nOficina registrada: 123 Example Street, Your City",
			'zh'    => "The Culina Academy LTD\n注册办事处：Your City 00000 阿尔伯马尔街45号 3楼",
			'zh-tw' => "The Culina Academy LTD\n註冊辦事處：Your City 00000 阿爾伯馬爾街45號 3樓",
		),
		'Culina Academy offers hands-on professional training for chefs, pastry chefs and restaurant managers. Book an appointment with one of our advisors for more information.' => array(
			'fr'    => "Culina Academy propose une formation professionnelle pratique pour les chefs, les pâtissiers et les directeurs de restaurant. Prenez rendez-vous avec l’un de nos conseillers pour plus d’informations.",
			'it'    => "Culina Academy offre una formazione professionale pratica per chef, pasticceri e direttori di ristorante. Prenota un appuntamento con uno dei nostri consulenti per maggiori informazioni.",
			'es'    => "Culina Academy ofrece formación profesional práctica para chefs, pasteleros y directores de restaurante. Reserve una cita con uno de nuestros asesores para obtener más información.",
			'zh'    => "Culina Academy 为厨师、糕点师和餐厅经理提供实践型专业培训。请预约我们的顾问以获取更多信息。",
			'zh-tw' => "Culina Academy 為廚師、糕點師和餐廳經理提供實踐型專業培訓。請預約我們的顧問以獲取更多資訊。",
		),

		// ── Footer bottom line ───────────────────────────────────────────
		'The Culina Academy is a professional culinary school, offering professional chef courses in Your City with real hands-on training in high-level kitchens.' => array(
			'fr'    => "Culina Academy est une école culinaire professionnelle qui propose une formation pratique de chef à Your City.",
			'it'    => "Culina Academy è una scuola di cucina professionale che offre una formazione pratica per chef a Your City.",
			'es'    => "Culina Academy es una escuela culinaria profesional que ofrece formación práctica para chefs en Your City.",
			'zh'    => "Culina Academy 是一所专业烹饪学校，在 Your City 提供实践型厨师培训。",
			'zh-tw' => "Culina Academy 是一所專業烹飪學校，在 Your City 提供實踐型廚師培訓。",
		),
		'Culina Academy is a professional culinary school offering hands-on chef training in Your City.' => array(
			'fr'    => "Culina Academy est une école culinaire professionnelle qui propose une formation pratique de chef à Your City.",
			'it'    => "Culina Academy è una scuola di cucina professionale che offre una formazione pratica per chef a Your City.",
			'es'    => "Culina Academy es una escuela culinaria profesional que ofrece formación práctica para chefs en Your City.",
			'zh'    => "Culina Academy 是一所专业烹饪学校，在 Your City 提供实践型厨师培训。",
			'zh-tw' => "Culina Academy 是一所專業烹飪學校，在 Your City 提供實踐型廚師培訓。",
		),

		// ── Policy link titles ───────────────────────────────────────────
		'Privacy Policy'                          => array( 'fr' => 'Politique de confidentialité',           'it' => 'Informativa sulla privacy',        'es' => 'Política de privacidad',              'zh' => '隐私政策',     'zh-tw' => '隱私政策' ),
		'Terms & Conditions'                      => array( 'fr' => 'Termes et conditions',                   'it' => 'Termini e condizioni',             'es' => 'Términos y condiciones',              'zh' => '条款与条件',   'zh-tw' => '條款與條件' ),
		'Terms and Conditions'                    => array( 'fr' => 'Termes et conditions',                   'it' => 'Termini e condizioni',             'es' => 'Términos y condiciones',              'zh' => '条款与条件',   'zh-tw' => '條款與條件' ),
		'Terms Conditions'                        => array( 'fr' => 'Termes et conditions',                   'it' => 'Termini e condizioni',             'es' => 'Términos y condiciones',              'zh' => '条款与条件',   'zh-tw' => '條款與條件' ),
		'Termini Condizioni'                      => array( 'fr' => 'Termes et conditions',                   'it' => 'Termini e condizioni',             'es' => 'Términos y condiciones',              'zh' => '条款与条件',   'zh-tw' => '條款與條件' ),
		'Cookie Policy'                           => array( 'fr' => 'Politique de cookies',                   'it' => 'Politica sui cookie',              'es' => 'Política de cookies',                 'zh' => 'Cookie政策',  'zh-tw' => 'Cookie政策' ),
		'Cookies Policy'                          => array( 'fr' => 'Politique de cookies',                   'it' => 'Politica sui cookie',              'es' => 'Política de cookies',                 'zh' => 'Cookie政策', 'zh-tw' => 'Cookie政策' ),
		'Complaints Policy'                       => array( 'fr' => 'Politique de réclamations',              'it' => 'Politica dei reclami',             'es' => 'Política de reclamaciones',           'zh' => '投诉政策',     'zh-tw' => '投訴政策' ),
		'Complaints Policy and Procedure'         => array( 'fr' => 'Politique de réclamations et procédure', 'it' => 'Procedura e politica dei reclami', 'es' => 'Política y procedimiento de reclamaciones','zh' => '投诉政策与程序', 'zh-tw' => '投訴政策與程序' ),
		'Politica e procedura per i reclami'      => array( 'fr' => 'Politique de réclamations et procédure', 'it' => 'Procedura e politica dei reclami', 'es' => 'Política y procedimiento de reclamaciones','zh' => '投诉政策与程序', 'zh-tw' => '投訴政策與程序' ),
		'Attendance Policy'                       => array( 'fr' => 'Politique de présence et de ponctualité','it' => 'Politica di presenze e puntualità', 'es' => 'Política de asistencia y puntualidad','zh' => '出勤政策',     'zh-tw' => '出勤政策' ),
		'Attendance and Punctuality Policy'       => array( 'fr' => 'Politique de présence et de ponctualité','it' => 'Politica di presenze e puntualità', 'es' => 'Política de asistencia y puntualidad','zh' => '出勤与准时政策', 'zh-tw' => '出勤與準時政策' ),
		'Politica di partecipazione e puntualità' => array( 'fr' => 'Politique de présence et de ponctualité','it' => 'Politica di presenze e puntualità', 'es' => 'Política de asistencia y puntualidad','zh' => '出勤与准时政策', 'zh-tw' => '出勤與準時政策' ),
		'politica sulla riservatezza'             => array( 'fr' => 'Politique de confidentialité',           'it' => 'Informativa sulla privacy',        'es' => 'Política de privacidad',              'zh' => '隐私政策',     'zh-tw' => '隱私政策' ),
		'Politica sui cookie'                     => array( 'fr' => 'Politique de cookies',                   'it' => 'Politica sui cookie',              'es' => 'Política de cookies',                 'zh' => 'Cookie政策',  'zh-tw' => 'Cookie政策' ),
		'Blog'                                    => array( 'fr' => 'Blog',                                   'it' => 'Blog',                             'es' => 'Blog',                                'zh' => '博客',         'zh-tw' => '部落格' ),
		'Contact'                                 => array( 'fr' => 'Contact',                                'it' => 'Contatto',                         'es' => 'Contacto',                            'zh' => '联系',         'zh-tw' => '聯絡' ),
		'Contatto'                                => array( 'fr' => 'Contact',                                'it' => 'Contatto',                         'es' => 'Contacto',                            'zh' => '联系',         'zh-tw' => '聯絡' ),

		// ── Breadcrumbs ──────────────────────────────────────────────────
		'Home'       => array( 'fr' => 'Accueil', 'it' => 'Home',        'es' => 'Inicio',               'zh' => '首页',   'zh-tw' => '首頁' ),
		'Breadcrumb' => array( 'fr' => "Fil d'Ariane", 'it' => 'Percorso', 'es' => 'Ruta de navegación', 'zh' => '导航路径', 'zh-tw' => '導覽路徑' ),

		// ── Course carousel / cards ──────────────────────────────────────
		'Our Courses'                                     => array( 'fr' => 'Nos Cours',                                                    'it' => 'I Nostri Corsi',                                    'es' => 'Nuestros Cursos',                                    'zh' => '我们的课程',     'zh-tw' => '我們的課程' ),
		'Our School'                                      => array( 'fr' => 'Notre école',                                                 'it' => 'La Nostra Scuola',                                 'es' => 'Nuestra Escuela',                                   'zh' => '我们的学校',     'zh-tw' => '我們的學校' ),
		'Careers'                                         => array( 'fr' => 'Carrières',                                                  'it' => 'Percorsi Professionali',                             'es' => 'Carreras',                                          'zh' => '职业发展',       'zh-tw' => '職業發展' ),
		'Online Courses'                                  => array( 'fr' => 'Cours en ligne',                                              'it' => 'Corsi Online',                                    'es' => 'Cursos en Línea',                                   'zh' => '在线课程',       'zh-tw' => '線上課程' ),
		'How to Apply'                                    => array( 'fr' => 'Comment postuler',                                            'it' => 'Ammissioni',                                        'es' => 'Cómo solicitar',                                    'zh' => '如何申请',       'zh-tw' => '如何申請' ),
		'Introduction to the Professional Chef Online Course' => array( 'fr' => "Introduction au cours de chef professionnel en ligne",    'it' => "Corso Online Introduttivo Professionale per Chef", 'es' => "Introducción al curso de chef profesional en línea", 'zh' => '专业厨师在线课程介绍', 'zh-tw' => '專業廚師線上課程介紹' ),
		'Advanced Techniques Online Course' => array( 'fr' => "Cours en ligne de techniques avancées",          'it' => "Corso Online di Tecniche Avanzate",     'es' => "Curso en línea de técnicas avanzadas", 'zh' => '高级技巧在线课程', 'zh-tw' => '高級技巧線上課程' ),
		'Advanced Techniques Online Course' => array( 'fr' => "Cours en ligne de techniques avancées",          'it' => "Corso Online di Tecniche Avanzate",     'es' => "Curso en línea de técnicas avanzadas", 'zh' => '高级技巧在线课程', 'zh-tw' => '高級技巧線上課程' ),
		'Learn the art of <em>professional cooking.</em>' => array( 'fr' => "Apprenez l'art de la <em>cuisine professionnelle.</em>",        'it' => "Impara l'arte della <em>cucina professionale.</em>", 'es' => "Aprende el arte de la <em>cocina profesional.</em>", 'zh' => '学习<em>专业烹饪</em>的艺术。', 'zh-tw' => '學習<em>專業烹飪</em>的藝術。' ),
		'View Course'                                     => array( 'fr' => 'Voir le cours',                                                 'it' => 'Scopri il corso',                                   'es' => 'Ver el curso',                                       'zh' => '查看课程',       'zh-tw' => '查看課程' ),
		'Browse More Courses'                             => array( 'fr' => 'Voir plus de cours',                                            'it' => 'Esplora tutti i corsi',                             'es' => 'Ver más cursos',                                     'zh' => '浏览更多课程',   'zh-tw' => '瀏覽更多課程' ),
		'Location'                                        => array( 'fr' => 'Lieu',                                                          'it' => 'Sede',                                              'es' => 'Ubicación',                                          'zh' => '地点',           'zh-tw' => '地點' ),
		'Duration'                                        => array( 'fr' => 'Durée',                                                         'it' => 'Durata',                                            'es' => 'Duración',                                           'zh' => '时长',           'zh-tw' => '時長' ),

		// ── Course duration units ────────────────────────────────────────
		'6 months'  => array( 'fr' => '6 mois',     'it' => '6 mesi',       'es' => '6 meses',  'zh' => '6个月',  'zh-tw' => '6個月' ),
		'3 months'  => array( 'fr' => '3 mois',     'it' => '3 mesi',       'es' => '3 meses',  'zh' => '3个月',  'zh-tw' => '3個月' ),
		'12 months' => array( 'fr' => '12 mois',    'it' => '12 mesi',      'es' => '12 meses', 'zh' => '12个月', 'zh-tw' => '12個月' ),
		'1 month'   => array( 'fr' => '1 mois',     'it' => '1 mese',       'es' => '1 mes',    'zh' => '1个月',  'zh-tw' => '1個月' ),
		'2 weeks'   => array( 'fr' => '2 semaines', 'it' => '2 settimane',  'es' => '2 semanas','zh' => '2周',    'zh-tw' => '2週' ),
		'1 week'    => array( 'fr' => '1 semaine',  'it' => '1 settimana',  'es' => '1 semana', 'zh' => '1周',    'zh-tw' => '1週' ),

		// ── Testimonials CTA ─────────────────────────────────────────────
		'More Reviews'        => array( 'fr' => "Plus d'avis",      'it' => 'Altre recensioni',      'es' => 'Más reseñas',      'zh' => '更多评价', 'zh-tw' => '更多評價' ),
		'Read More Reviews'   => array( 'fr' => "Plus d'avis",      'it' => 'Altre recensioni',      'es' => 'Más reseñas',      'zh' => '更多评价', 'zh-tw' => '更多評價' ),

		// ── Menu items ───────────────────────────────────────────────────
		'About Us'               => array( 'fr' => 'À propos de nous',          'it' => 'Chi Siamo',               'es' => 'Sobre Nosotros',              'zh' => '关于我们',     'zh-tw' => '關於我們' ),
		'Online Courses'         => array( 'fr' => 'Cours en Ligne',            'it' => 'Corsi Online',            'es' => 'Cursos En Línea',             'zh' => '在线课程',     'zh-tw' => '線上課程' ),
		'Cooking Courses'        => array( 'fr' => 'Cours de Cuisine',          'it' => 'Corsi di Cucina',         'es' => 'Cursos de Cocina',            'zh' => '烹饪课程',     'zh-tw' => '烹飪課程' ),
		'International Students' => array( 'fr' => 'Étudiants Internationaux',  'it' => 'Studenti Internazionali', 'es' => 'Estudiantes Internacionales', 'zh' => '国际学生',     'zh-tw' => '國際學生' ),
		'Restaurant Partners'    => array( 'fr' => 'Restaurants partenaires',   'it' => 'Partner Ristoranti',      'es' => 'Socios Restaurantes',         'zh' => '餐厅合作伙伴', 'zh-tw' => '餐廳合作夥伴' ),
		'Reviews'                => array( 'fr' => 'Avis',                      'it' => 'Recensioni',              'es' => 'Reseñas',                     'zh' => '评价',         'zh-tw' => '評價' ),
	);
}

// ─────────────────────────────────────────────────────────────────────────────
// DIRECT TRANSLATION INTERCEPT
// Hook into WordPress's gettext filter for the 'pll_string' domain.
// This fires for every pll__() call and overrides with our map — no DB needed.
// ─────────────────────────────────────────────────────────────────────────────
add_filter( 'gettext', 'cal_pll_string_intercept', 5, 3 );
function cal_pll_string_intercept( $translation, $text, $domain ) {
	if ( 'pll_string' !== $domain ) {
		return $translation;
	}

	if ( ! function_exists( 'pll_current_language' ) ) {
		return $translation;
	}

	static $lang = null;
	static $map  = null;

	if ( null === $lang ) {
		$lang = pll_current_language( 'slug' );
	}

	if ( ! $lang || 'en' === $lang ) {
		return $translation;
	}

	if ( null === $map ) {
		$full = cal_footer_trans_map();
		$map  = array();
		foreach ( $full as $en => $langs ) {
			if ( isset( $langs[ $lang ] ) && '' !== $langs[ $lang ] ) {
				$map[ $en ] = $langs[ $lang ];
			}
		}
	}

	if ( isset( $map[ $text ] ) ) {
		return $map[ $text ];
	}

	return $translation;
}
function cal_footer_write_translations( $extra_map = array() ) {
	if ( ! function_exists( 'PLL' ) || ! PLL() ) {
		return array( 'error' => 'Polylang not active.' );
	}

	$full_map = array_merge( cal_footer_trans_map(), $extra_map );
	$results  = array();

	foreach ( array( 'fr', 'it', 'es', 'zh', 'zh-tw' ) as $slug ) {
		$lang = PLL()->model->get_language( $slug );
		if ( ! $lang ) { $results[ $slug ] = 'language not found'; continue; }

		$existing = get_term_meta( $lang->term_id, '_pll_strings_translations', true );
		$existing = is_array( $existing ) ? $existing : array();

		// Build keyed index for fast lookup
		$index = array();
		foreach ( $existing as $k => $pair ) {
			if ( is_array( $pair ) && isset( $pair[0] ) ) {
				$index[ $pair[0] ] = $k;
			}
		}

		$written = 0;
		foreach ( $full_map as $english => $langs ) {
			if ( ! isset( $langs[ $slug ] ) || '' === $langs[ $slug ] ) continue;
			$translated = $langs[ $slug ];
			if ( isset( $index[ $english ] ) ) {
				$existing[ $index[ $english ] ][1] = $translated;
			} else {
				$existing[] = array( $english, $translated );
			}
			$written++;
		}

		update_term_meta( $lang->term_id, '_pll_strings_translations', $existing );
		$results[ $slug ] = $written . ' strings written';
	}

	// Flush Polylang caches
	delete_transient( 'pll_languages_list' );
	if ( method_exists( PLL()->model->languages, 'clean_cache' ) ) {
		PLL()->model->languages->clean_cache();
	}
	do_action( 'pll_save_strings_translations' );

	return $results;
}

// ─────────────────────────────────────────────────────────────────────────────
// Footer render override: apply translations directly without relying on pll__()
// ─────────────────────────────────────────────────────────────────────────────
function cal_footer_get_translations( $lang ) {
	$map = cal_footer_trans_map();
	$out = array();
	foreach ( $map as $en => $langs ) {
		if ( isset( $langs[ $lang ] ) ) {
			$out[ $en ] = $langs[ $lang ];
		}
	}
	return $out;
}

// ─────────────────────────────────────────────────────────────────────────────
// Admin URL trigger
// ─────────────────────────────────────────────────────────────────────────────
add_action( 'admin_init', function () {
	$action = isset( $_GET['cal_footer_trans'] ) ? sanitize_key( $_GET['cal_footer_trans'] ) : '';
	if ( ! $action ) return;
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Footer Translations</title>';
	echo '<style>body{font-family:monospace;padding:20px;background:#f0f0f0;}pre{background:#fff;padding:15px;border:1px solid #ccc;}</style></head><body>';

	if ( 'apply' === $action ) {
		// Also pull actual ACF values and add them to the map if they differ from known keys
		$extra = array();
		if ( function_exists( 'get_field' ) ) {
			$acf_fields = array( 'cal_footer_cta_title','cal_footer_gallery_heading','cal_footer_about_heading','cal_footer_about','cal_footer_contact_heading','cal_footer_address','cal_footer_policies_heading','cal_footer_bottom_line' );
			$base = cal_footer_trans_map();
			foreach ( $acf_fields as $f ) {
				$v = get_field( $f, 'option' );
				if ( is_string( $v ) && '' !== $v && ! isset( $base[ $v ] ) ) {
					foreach ( $base as $known => $langs ) {
						if ( strtolower( trim( $v ) ) === strtolower( trim( $known ) ) ) {
							$extra[ $v ] = $langs;
							break;
						}
					}
				}
			}
		}

		$results = cal_footer_write_translations( $extra );
		echo '<h2>✅ Translations Applied</h2><pre>';
		foreach ( $results as $lang => $msg ) echo "  {$lang}: {$msg}\n";
		if ( isset( $results['error'] ) ) echo "  ERROR: " . $results['error'] . "\n";
		echo '</pre>';
		echo '<p><a href="' . esc_url( admin_url() ) . '">← Dashboard</a></p>';
	}

	echo '</body></html>';
	exit;
} );
