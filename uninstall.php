<?php

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}


defined( 'ACOBOT_SLUG' ) || define( 'ACOBOT_SLUG', '_acobot_' );

$opts   = wp_load_alloptions();
foreach ( $opts as $key => $value ) {
	if ( strpos( $key, ACOBOT_SLUG ) === 0 ) {
		delete_option( $key );
	}
}

require_once dirname( __FILE__ ) . '/acobot-woocommerce.php';

if ( 
  in_array( 
    'woocommerce/woocommerce.php', 
    apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) 
  ) 
) {
    aco_wc_clear_keys();
}
