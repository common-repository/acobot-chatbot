<?php
/**
 * WooCommerce REST API
 *
 */

/**
 * Create keys.
 */

function aco_wc_create_keys() {
	global $wpdb;
	$key = get_option( ACOBOT_SLUG . 'api' );
	if(strpos($key, ".") > 0) {
		$key = substr($key, strpos($key, ".") + 1);
	}
	
	$ecomm_key_secret = aco_set_wc_keys();
	if(empty($ecomm_key_secret) or sizeof($ecomm_key_secret) != 2) {
		return;
	}
	
	$description = "Acobot";
	$user = wp_get_current_user();

	$permissions = "read";
	$consumer_key    = $ecomm_key_secret[0];
	$consumer_secret = $ecomm_key_secret[1];
	
	$count = $wpdb->get_var( "SELECT COUNT(key_id) FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_secret = '".$consumer_secret."'");
	if($count > 0) return;
	
	$wpdb->insert(
		$wpdb->prefix . 'woocommerce_api_keys',
		array(
			'user_id'         => $user->ID,
			'description'     => $description,
			'permissions'     => $permissions,
			'consumer_key'    => hash_hmac( 'sha256', $consumer_key, 'wc-api' ),
			'consumer_secret' => $consumer_secret,
			'truncated_key'   => substr( $consumer_key, -7 ),
		),
		array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);
	aco_wc_deliver();
}

function aco_wc_clear_keys() {
	global $wpdb;
	$aco_woocom_key = $wpdb->get_row( "SELECT COUNT(key_id) FROM {$wpdb->prefix}woocommerce_api_keys WHERE description = 'Acobot'");
	if(!empty($aco_woocom_key)) {
		$truncated_key = $aco_woocom_key->truncated_key;
		$key = get_option( ACOBOT_SLUG . 'api' );
		$http_args = array();
		$pos = strpos($key, ".");
		if($pos > 0) {
			$args = array(
			    'method' => 'GET'
			);
			$gid = substr($key, 0, $pos);
			// api/ecom/keys/set
			// api/ecom/keys/clear
			wp_remote_request("https://acobot.ai/api/ecom/keys/clear/$gid/$truncated_key", $args);
		}
	}
	
}

/*

			'coupon.created'   => array(
				'woocommerce_process_shop_coupon_meta',
				'woocommerce_new_coupon',
			),
			'coupon.updated'   => array(
				'woocommerce_process_shop_coupon_meta',
				'woocommerce_update_coupon',
			),
			'coupon.deleted'   => array(
				'wp_trash_post',
			),
			'coupon.restored'  => array(
				'untrashed_post',
			),
			'product.created'  => array(
				'woocommerce_process_product_meta',
				'woocommerce_new_product',
				'woocommerce_new_product_variation',
			),

			'product.updated'  => array(
				'woocommerce_process_product_meta',
				'woocommerce_update_product',
				'woocommerce_update_product_variation',
			),
			'product.deleted'  => array(
				'wp_trash_post',
			),
			'product.restored' => array(
				'untrashed_post',
			),


*/
// class-wc-webhook.php

$wooc_actions = array(
	'woocommerce_process_shop_coupon_meta',
	'woocommerce_new_coupon',
	'woocommerce_update_coupon',
	'woocommerce_process_product_meta',
	'woocommerce_new_product',
	'woocommerce_new_product_variation',
	'woocommerce_update_product',
	'woocommerce_update_product_variation',
	'wp_trash_post',
	'untrashed_post',
);
foreach($wooc_actions as $wooc_action) {
	wp_schedule_single_event(time() + 60, "aco_wc_contents_update");
}

add_action('aco_wc_contents_update', 'aco_wc_deliver');

function aco_wc_deliver() {
	global $wpdb;
	$aco_woocom_key = $wpdb->get_row( "SELECT truncated_key FROM {$wpdb->prefix}woocommerce_api_keys WHERE description = 'Acobot'");
	if(!empty($aco_woocom_key)) {
		$truncated_key = $aco_woocom_key->truncated_key;
		$key = get_option( ACOBOT_SLUG . 'api' );
		$http_args = array();
		$pos = strpos($key, ".");
		if($pos > 0) {
			$args = array(
			    'method' => 'GET'
			);
			$gid = substr($key, 0, $pos);
			wp_remote_request("https://acobot.ai/api/ecom/build/$gid/$truncated_key", $args);
		}
	}
}


function aco_set_wc_keys() {
	$key = get_option( ACOBOT_SLUG . 'api' );
	$pos = strpos($key, ".");
	if($pos > 0) {
		$gid = substr($key, 0, $pos);
		$key = substr($key, strpos($key, ".") + 1);
		$args = array(
		    'method' => 'GET'
		);
		
		$url = urlencode(get_home_url());
		// api/ecom/keys/set
		$request = wp_remote_request("https://acobot.ai/api/ecom/keys/set/$gid/$key?url=$url", $args);		
		if( is_wp_error( $request ) ) {
			return false; // Bail early
		}

		$body = wp_remote_retrieve_body( $request );		
		return json_decode($body, true);
	}
	return array();
}


