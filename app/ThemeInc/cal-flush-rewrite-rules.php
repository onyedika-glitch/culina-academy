<?php

$key = $_GET['key'] ?? '';
if ( $key !== 'opencode' ) {
	http_response_code( 403 );
	echo 'forbidden';
	exit;
}

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

flush_rewrite_rules( true );
echo 'ok';

