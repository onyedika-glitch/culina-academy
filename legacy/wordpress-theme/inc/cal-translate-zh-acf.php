<?php

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) {
	http_response_code( 403 );
	echo 'forbidden';
	exit;
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	die( 'Polylang not active' );
}

if ( ! function_exists( 'get_fields' ) || ! function_exists( 'update_field' ) ) {
	die( 'ACF not active' );
}

$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'page';
$source_lang = isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : 'en';
$langs_param = isset( $_GET['langs'] ) ? sanitize_text_field( wp_unslash( $_GET['langs'] ) ) : 'zh,zh-tw';
$target_langs = array_values( array_filter( array_map( 'trim', explode( ',', $langs_param ) ) ) );
$force = isset( $_GET['force'] ) && 'yes' === sanitize_text_field( wp_unslash( $_GET['force'] ) );
$max_chunk_chars = isset( $_GET['chunk'] ) ? max( 200, (int) $_GET['chunk'] ) : 1500;
$delay_ms = isset( $_GET['delay'] ) ? max( 0, (int) $_GET['delay'] ) : 500;
$mode = isset( $_GET['mode'] ) ? sanitize_key( wp_unslash( $_GET['mode'] ) ) : 'translate';
$only_param = isset( $_GET['only'] ) ? sanitize_text_field( wp_unslash( $_GET['only'] ) ) : '';
$only_ids = array();
if ( '' !== $only_param ) {
	$only_ids = array_values(
		array_filter(
			array_map(
				'intval',
				array_map( 'trim', explode( ',', $only_param ) )
			)
		)
	);
}

$lang_to_gt = array(
	'zh'    => 'zh-CN',
	'zh-tw' => 'zh-TW',
);

foreach ( $target_langs as $slug ) {
	if ( ! PLL()->model->get_language( $slug ) ) {
		die( 'Missing Polylang language: ' . esc_html( $slug ) );
	}
	if ( ! isset( $lang_to_gt[ $slug ] ) ) {
		die( 'Missing Google Translate mapping for: ' . esc_html( $slug ) );
	}
}

function cal_gt_translate_small( $text, $target, $delay_ms = 0, $attempts = 3 ) {
	if ( '' === trim( (string) $text ) ) {
		return (string) $text;
	}

	$text = (string) $text;
	if ( function_exists( 'wp_check_invalid_utf8' ) ) {
		$text = wp_check_invalid_utf8( $text, true );
	}
	$text = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text );

	$GLOBALS['cal_gt_last_error'] = '';

	for ( $i = 1; $i <= $attempts; $i++ ) {
		$url = add_query_arg(
			array(
				'client' => 'gtx',
				'sl'     => 'en',
				'tl'     => $target,
				'dt'     => 't',
			),
			'https://translate.googleapis.com/translate_a/single'
		);

		$r = wp_remote_post(
			$url,
			array(
				'timeout' => 60,
				'body'    => array(
					'q' => $text,
				),
			)
		);

		if ( is_wp_error( $r ) ) {
			$GLOBALS['cal_gt_last_error'] = $r->get_error_message();
		} else {
			$code = (int) wp_remote_retrieve_response_code( $r );
			$body = (string) wp_remote_retrieve_body( $r );
			if ( 200 !== $code ) {
				$len = function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : strlen( $text );
				$GLOBALS['cal_gt_last_error'] = 'HTTP ' . $code . ' (len ' . $len . ')';
			} else {
				$data = json_decode( $body, true );
				if ( is_array( $data ) && isset( $data[0] ) ) {
					$out = '';
					foreach ( $data[0] as $seg ) {
						if ( isset( $seg[0] ) ) {
							$out .= $seg[0];
						}
					}
					return $out;
				}
				$GLOBALS['cal_gt_last_error'] = 'Invalid JSON response';
			}
		}

		if ( $i < $attempts ) {
			if ( $delay_ms > 0 ) {
				usleep( $delay_ms * 1000 );
			}
			if ( $delay_ms > 0 ) {
				$delay_ms = (int) min( 4000, $delay_ms * 2 );
			}
		}
	}

	return null;
}

function cal_split_keep_delims( $text, $max_chunk_chars ) {
	$parts = preg_split( "/(\n{2,})/", (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE );
	$chunks = array();
	$buf = '';

	$strlen = function ( $s ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( (string) $s, 'UTF-8' ) : strlen( (string) $s );
	};
	$substr = function ( $s, $start, $len = null ) {
		if ( function_exists( 'mb_substr' ) ) {
			return null === $len ? mb_substr( (string) $s, $start, null, 'UTF-8' ) : mb_substr( (string) $s, $start, $len, 'UTF-8' );
		}
		return null === $len ? substr( (string) $s, $start ) : substr( (string) $s, $start, $len );
	};

	$force_split = function ( $s ) use ( $max_chunk_chars, $strlen, $substr ) {
		$out = array();
		$rem = (string) $s;
		while ( $strlen( $rem ) > $max_chunk_chars ) {
			$probe = $substr( $rem, 0, $max_chunk_chars + 1 );
			$cut = 0;
			$breaks = array( "\n", ' ', '.', '。', '!', '?', ';', ':', ',', '，' );
			foreach ( $breaks as $b ) {
				$pos = function_exists( 'mb_strrpos' ) ? mb_strrpos( $probe, $b, 0, 'UTF-8' ) : strrpos( $probe, $b );
				if ( false !== $pos ) {
					$pos += $strlen( $b );
					if ( $pos > $cut ) {
						$cut = $pos;
					}
				}
			}
			if ( $cut < 1 || $cut > $max_chunk_chars + 1 ) {
				$cut = $max_chunk_chars;
			}
			$out[] = $substr( $rem, 0, $cut );
			$rem = $substr( $rem, $cut );
		}
		if ( '' !== $rem ) {
			$out[] = $rem;
		}
		return $out;
	};

	foreach ( $parts as $p ) {
		if ( '' === $p ) {
			continue;
		}
		$next = $buf . $p;
		$len = $strlen( $next );
		if ( $len > $max_chunk_chars && '' !== $buf ) {
			$chunks[] = $buf;
			$buf = $p;
			if ( $strlen( $buf ) > $max_chunk_chars ) {
				foreach ( $force_split( $buf ) as $s ) {
					$chunks[] = $s;
				}
				$buf = '';
			}
		} else {
			$buf = $next;
			if ( $strlen( $buf ) > $max_chunk_chars ) {
				foreach ( $force_split( $buf ) as $s ) {
					$chunks[] = $s;
				}
				$buf = '';
			}
		}
	}

	if ( '' !== $buf ) {
		$chunks[] = $buf;
	}

	return $chunks;
}

function cal_gt_translate_large( $text, $target, $max_chunk_chars, $delay_ms = 0, $attempts = 3 ) {
	$chunks = cal_split_keep_delims( $text, $max_chunk_chars );
	$out = '';
	foreach ( $chunks as $chunk ) {
		$t = cal_gt_translate_small( $chunk, $target, $delay_ms, $attempts );
		if ( null === $t ) {
			return null;
		}
		$out .= $t;
		if ( $delay_ms > 0 ) {
			usleep( $delay_ms * 1000 );
		}
	}
	return $out;
}

function cal_protect_markup( $content ) {
	$map = array();
	$i = 0;

	$patterns = array(
		'/<!--[\s\S]*?-->/',
		'/<[^>]+>/',
		'/\[[^\[\]]+\]/',
	);

	foreach ( $patterns as $pattern ) {
		$content = preg_replace_callback(
			$pattern,
			function ( $m ) use ( &$map, &$i ) {
				$token = '__CAL' . $i . '__';
				$map[ $token ] = $m[0];
				$i++;
				return $token;
			},
			$content
		);
	}

	return array( $content, $map );
}

function cal_unprotect_markup( $content, $map ) {
	if ( empty( $map ) ) {
		return $content;
	}
	return strtr( (string) $content, $map );
}

function cal_should_skip_translation( $value ) {
	if ( ! is_string( $value ) ) {
		return true;
	}
	$v = trim( $value );
	if ( '' === $v ) {
		return true;
	}
	if ( preg_match( '/^https?:\\/\\//i', $v ) ) {
		return true;
	}
	if ( false !== strpos( $v, '@' ) && preg_match( '/\\S+@\\S+/', $v ) ) {
		return true;
	}
	if ( preg_match( '/^\\+?[0-9\\s().-]{7,}$/', $v ) ) {
		return true;
	}
	return false;
}

function cal_translate_any_value( $value, $gt_code, $max_chunk_chars, $delay_ms, $is_html = false ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			$is_link_title = ( 'title' === $k && isset( $value['url'] ) );
			$value[ $k ] = cal_translate_any_value( $v, $gt_code, $max_chunk_chars, $delay_ms, $is_html || $is_link_title );
		}
		return $value;
	}

	if ( ! is_string( $value ) ) {
		return $value;
	}

	if ( cal_should_skip_translation( $value ) ) {
		return $value;
	}

	$text = (string) $value;

	if ( $is_html || false !== strpos( $text, '<' ) || false !== strpos( $text, '[' ) ) {
		list( $protected, $map ) = cal_protect_markup( $text );
		$t = cal_gt_translate_large( $protected, $gt_code, $max_chunk_chars, $delay_ms );
		if ( null === $t ) {
			return $value;
		}
		return cal_unprotect_markup( $t, $map );
	}

	$t = cal_gt_translate_small( $text, $gt_code, $delay_ms );
	return null === $t ? $value : $t;
}

$source_posts = get_posts(
	array(
		'post_type'      => $post_type,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'lang'           => $source_lang,
	)
);

if ( empty( $source_posts ) ) {
	$source_posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	);
	$source_posts = array_values(
		array_filter(
			$source_posts,
			function ( $p ) use ( $source_lang ) {
				return function_exists( 'pll_get_post_language' ) && pll_get_post_language( $p->ID ) === $source_lang;
			}
		)
	);
}

echo '<pre style="font-family:monospace;padding:20px;font-size:13px;">';
echo "=== Translating ACF fields (post_type={$post_type}) ===\n\n";
echo "Source language: {$source_lang}\n";
echo 'Targets: ' . implode( ', ', $target_langs ) . "\n";
echo "Delay per request: {$delay_ms}ms\n";
echo "Chunk size: {$max_chunk_chars}\n";
echo "Mode: {$mode}\n";
echo 'Found ' . count( $source_posts ) . " source posts.\n\n";

$updated = 0;
$skipped = 0;
$failed  = 0;

foreach ( $source_posts as $src ) {
	if ( ! empty( $only_ids ) && ! in_array( (int) $src->ID, $only_ids, true ) ) {
		continue;
	}

	$fields = get_fields( $src->ID );
	if ( ! is_array( $fields ) || empty( $fields ) ) {
		continue;
	}

	$src_hash = md5( wp_json_encode( $fields ) );
	$trans = pll_get_post_translations( $src->ID );

	echo "Post: [{$src->ID}] {$src->post_title}\n";

	foreach ( $target_langs as $lang_slug ) {
		$target_id = isset( $trans[ $lang_slug ] ) ? (int) $trans[ $lang_slug ] : 0;
		if ( ! $target_id ) {
			echo "  [$lang_slug] missing translation post — skip\n";
			$skipped++;
			continue;
		}

		$done_hash = (string) get_post_meta( $target_id, '_cal_auto_translated_acf_hash', true );
		if ( ! $force && $done_hash === $src_hash ) {
			echo "  [$lang_slug] already up-to-date — skip\n";
			$skipped++;
			continue;
		}

		$gt_code = $lang_to_gt[ $lang_slug ];
		$out_fields = array();

		if ( 'copy' === $mode ) {
			$out_fields = $fields;
		} else {
			foreach ( $fields as $name => $val ) {
				$obj = get_field_object( $name, $src->ID, false, false );
				$type = is_array( $obj ) && ! empty( $obj['type'] ) ? $obj['type'] : '';
				$is_html = 'wysiwyg' === $type;

				$out_fields[ $name ] = cal_translate_any_value( $val, $gt_code, $max_chunk_chars, $delay_ms, $is_html );
			}
		}

		$ok = true;
		foreach ( $out_fields as $name => $val ) {
			$r = update_field( $name, $val, $target_id );
			if ( false === $r ) {
				$ok = false;
			}
		}

		if ( ! $ok ) {
			echo "  [$lang_slug] update FAILED\n";
			$failed++;
			continue;
		}

		update_post_meta( $target_id, '_cal_auto_translated_acf_hash', $src_hash );
		update_post_meta( $target_id, '_cal_auto_translated_acf_at', gmdate( 'c' ) );
		update_post_meta( $target_id, '_cal_auto_translated_acf_from', $src->ID );
		update_post_meta( $target_id, '_cal_auto_translated_acf_lang', $lang_slug );

		echo "  [$lang_slug] updated (post ID {$target_id})\n";
		$updated++;
	}

	echo "\n";
}

echo "─────────────────────────────────────\n";
echo "Done. Updated: $updated | Skipped: $skipped | Failed: $failed\n";
echo '</pre>';
