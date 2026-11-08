<?php
/**
 * Creates zh (Simplified) and zh-tw (Traditional) translated page records
 * for every published English page, skipping blog posts.
 *
 * Access: http://q.local/wp-content/themes/generatepress/inc/cal-create-zh-pages.php?key=opencode
 *
 * Safe to run multiple times — skips pages that already have a translation.
 */

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) { http_response_code( 403 ); echo 'forbidden'; exit; }

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	die( 'Polylang not active' );
}

// ── Config ────────────────────────────────────────────────────────────────────
$target_langs = array( 'zh', 'zh-tw' );

// Slug map: English page slug → translated slug for each language
// Add more as needed. Pages not listed get auto-slugs.
$slug_map = array(
	'zh' => array(
		'home'                  => 'zhuye',
		'about-us'              => 'guanyu-women',
		'cooking-courses'       => 'pengren-kecheng',
		'online-courses'        => 'zaixian-kecheng',
		'international-students'=> 'guoji-xuesheng',
		'restaurant-partners'   => 'canting-hezuo',
		'reviews'               => 'pingjia',
		'contact-us'            => 'lianxi-women',
		'faq'                   => 'changjian-wenti',
		'how-to-apply'          => 'ruhe-shenqing',
		'our-school'            => 'women-de-xuexiao',
		'careers'               => 'zhaopin',
		'privacy-policy'        => 'yinsi-zhengce',
		'terms-and-conditions'  => 'tiaokuan-tiaojian',
		'cookie-policy'         => 'cookie-zhengce',
		'complaints-policy'     => 'tousu-zhengce',
		'attendance-policy'     => 'chuqin-zhengce',
	),
	'zh-tw' => array(
		'home'                  => 'zhuye-tw',
		'about-us'              => 'guanyu-women-tw',
		'cooking-courses'       => 'pengren-kecheng-tw',
		'online-courses'        => 'zaixian-kecheng-tw',
		'international-students'=> 'guoji-xuesheng-tw',
		'restaurant-partners'   => 'canting-hezuo-tw',
		'reviews'               => 'pingjia-tw',
		'contact-us'            => 'lianxi-women-tw',
		'faq'                   => 'changjian-wenti-tw',
		'how-to-apply'          => 'ruhe-shenqing-tw',
		'our-school'            => 'women-de-xuexiao-tw',
		'careers'               => 'zhaopin-tw',
		'privacy-policy'        => 'yinsi-zhengce-tw',
		'terms-and-conditions'  => 'tiaokuan-tiaojian-tw',
		'cookie-policy'         => 'cookie-zhengce-tw',
		'complaints-policy'     => 'tousu-zhengce-tw',
		'attendance-policy'     => 'chuqin-zhengce-tw',
	),
);

// Title map: English page slug → Chinese title
$title_map = array(
	'zh' => array(
		'home'                  => '首页',
		'about-us'              => '关于我们',
		'cooking-courses'       => '烹饪课程',
		'online-courses'        => '在线课程',
		'international-students'=> '国际学生',
		'restaurant-partners'   => '餐厅合作伙伴',
		'reviews'               => '评价',
		'contact-us'            => '联系我们',
		'faq'                   => '常见问题',
		'how-to-apply'          => '如何申请',
		'our-school'            => '我们的学校',
		'careers'               => '招聘',
		'privacy-policy'        => '隐私政策',
		'terms-and-conditions'  => '条款与条件',
		'cookie-policy'         => 'Cookie政策',
		'complaints-policy'     => '投诉政策',
		'attendance-policy'     => '出勤政策',
	),
	'zh-tw' => array(
		'home'                  => '首頁',
		'about-us'              => '關於我們',
		'cooking-courses'       => '烹飪課程',
		'online-courses'        => '線上課程',
		'international-students'=> '國際學生',
		'restaurant-partners'   => '餐廳合作夥伴',
		'reviews'               => '評價',
		'contact-us'            => '聯絡我們',
		'faq'                   => '常見問題',
		'how-to-apply'          => '如何申請',
		'our-school'            => '我們的學校',
		'careers'               => '招聘',
		'privacy-policy'        => '隱私政策',
		'terms-and-conditions'  => '條款與條件',
		'cookie-policy'         => 'Cookie政策',
		'complaints-policy'     => '投訴政策',
		'attendance-policy'     => '出勤政策',
	),
);

// ── Verify languages exist in Polylang ───────────────────────────────────────
$missing_langs = array();
foreach ( $target_langs as $slug ) {
	if ( ! PLL()->model->get_language( $slug ) ) {
		$missing_langs[] = $slug;
	}
}

if ( ! empty( $missing_langs ) ) {
	echo '<pre style="color:red;padding:20px;">';
	echo "ERROR: The following languages are not registered in Polylang:\n";
	foreach ( $missing_langs as $ml ) {
		echo "  - $ml\n";
	}
	echo "\nGo to WP Admin → Languages → Add New and add them first.\n";
	echo "For Simplified Chinese: locale zh_CN, slug zh\n";
	echo "For Traditional Chinese: locale zh_TW, slug zh-tw\n";
	echo '</pre>';
	exit;
}

// ── Get all English pages (exclude blog posts) ───────────────────────────────
$en_pages = get_posts( array(
	'post_type'      => 'page',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'lang'           => 'en',
) );

if ( empty( $en_pages ) ) {
	// Fallback: get all pages and filter by language
	$en_pages = get_posts( array(
		'post_type'      => 'page',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	) );
	$en_pages = array_filter( $en_pages, function( $p ) {
		return function_exists( 'pll_get_post_language' ) && pll_get_post_language( $p->ID ) === 'en';
	} );
}

echo '<pre style="font-family:monospace;padding:20px;font-size:13px;">';
echo "=== Creating zh / zh-tw page translations ===\n\n";
echo "Found " . count( $en_pages ) . " English pages.\n\n";

$created = 0;
$skipped = 0;

foreach ( $en_pages as $en_page ) {
	$en_slug     = $en_page->post_name;
	$en_template = get_page_template_slug( $en_page->ID );
	$en_trans    = pll_get_post_translations( $en_page->ID );

	echo "Page: [{$en_page->ID}] {$en_page->post_title} (/{$en_slug}/)\n";

	foreach ( $target_langs as $lang_slug ) {
		// Skip if translation already exists
		$lang_obj = PLL()->model->get_language( $lang_slug );
		if ( ! $lang_obj ) {
			echo "  [$lang_slug] language not found — skip\n";
			continue;
		}

		if ( ! empty( $en_trans[ $lang_slug ] ) ) {
			echo "  [$lang_slug] already exists (ID {$en_trans[$lang_slug]}) — skip\n";
			$skipped++;
			continue;
		}

		// Determine title and slug
		$new_title = $title_map[ $lang_slug ][ $en_slug ] ?? $en_page->post_title;
		$new_slug  = $slug_map[ $lang_slug ][ $en_slug ] ?? ( $en_slug . '-' . str_replace( '-', '', $lang_slug ) );

		// Create the translated page
		$new_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $new_title,
			'post_name'    => $new_slug,
			'post_content' => $en_page->post_content,
			'post_excerpt' => $en_page->post_excerpt,
			'post_parent'  => 0,
			'menu_order'   => $en_page->menu_order,
		) );

		if ( is_wp_error( $new_id ) ) {
			echo "  [$lang_slug] ERROR: " . $new_id->get_error_message() . "\n";
			continue;
		}

		// Copy page template
		if ( $en_template ) {
			update_post_meta( $new_id, '_wp_page_template', $en_template );
		}

		// Assign language
		pll_set_post_language( $new_id, $lang_slug );

		// Link as translation of the English page
		$en_trans[ $lang_slug ] = $new_id;
		pll_save_post_translations( $en_trans );

		echo "  [$lang_slug] Created ID $new_id → /{$new_slug}/ \"{$new_title}\"\n";
		$created++;
	}
	echo "\n";
}

echo "─────────────────────────────────────\n";
echo "Done. Created: $created  |  Skipped (already existed): $skipped\n\n";
echo "Next steps:\n";
echo "  1. Go to WP Admin → Pages and verify the new zh/zh-tw pages\n";
echo "  2. Visit http://q.local/wp-admin/?cal_footer_trans=apply to write string translations\n";
echo "  3. Visit http://q.local/zh/ and http://q.local/zh-tw/ to test\n";
echo '</pre>';
