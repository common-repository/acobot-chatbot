<?php
/**
 * Plugin Name: Acobot Chatbot
 * Plugin URI: https://acobot.ai/
 * Description: Acobot Chatbot
 * Version: 1.0.3
 * Author: Acobot LLC
 * Author URI: https://acobot.ai/
 * License: GPL2
 * Text-Domain: acobot
 * Domain Path: /languages
 */

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Abort loading if WordPress is upgrading
 */

require_once dirname( __FILE__ ) . '/acobot-woocommerce.php';

if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
	return;
}

add_action( 'activated_plugin', 'acobot_activate' );
function acobot_activate( $plugin ) {
	if ( $plugin == plugin_basename( __FILE__ ) ) {
		wp_safe_redirect( admin_url( 'options-general.php?page=acobot' ) );
		exit();
	}
}

add_action( 'plugins_loaded', 'acobot_run', 0 );
function acobot_run() {
	define( 'ACOBOT_SLUG', '_acobot_' );

	add_action( 'admin_init', 'acobot_register_settings' );
	add_action( 'admin_menu', 'acobot_menu' );
	add_action( 'wp_footer', 'acobot_add_script' );
}

function acobot_add_script() {
	$key = get_option( ACOBOT_SLUG . 'api' );
	if ( !empty( $key ) ) {
        echo "<script src=\"https://acobot.ai/js/w?key=$key\"></script>";
    }

}

function acobot_menu() {
	add_submenu_page( 'options-general.php', __( 'Acobot Settings', 'acobot' ), __( 'Acobot', 'acobot' ), 'manage_options', 'acobot', 'acobot_settings' );
}

function acobot_register_settings() {
	register_setting( 'acobot', ACOBOT_SLUG . 'api' );
	add_settings_section( 'acobot', __( 'Acobot', 'acobot' ), 'acobot_settings_section', 'acobot' );
	add_settings_field( ACOBOT_SLUG . 'api', __( 'API Key', 'acobot' ), 'acobot_settings_api', 'acobot', 'acobot' );
}

function acobot_settings_section() {
	echo '<p>' . __( 'Aco, the AI, engages your website visitors, answers their questions and turns them into sales leads, all by itself! ', 'acobot' ) . '</p>';
    echo '<p>' . __( 'No coding. No scripting. All you need to do is sign up and create an AI by providing your home URL. ', 'acobot' ) . '</p>';
    echo '<p>' . __( 'It couldn\'t be easier. Try it or you\'ll never know!', 'acobot' ) . '</p>';
}

function acobot_settings_api() {
	echo '<input type="text" class="regular-text" name="' . ACOBOT_SLUG . 'api" placeholder="' . __( 'API Key', 'acobot' ) . '" value="' . get_option( ACOBOT_SLUG . 'api' ) . '">';
	$key  = get_option( ACOBOT_SLUG . 'api' );
	if ( empty( $key ) ) {
		echo '<p class="description">' . sprintf( __( '%1$sSign up%2$s for API key.', 'acobot' ), '<a target="_new" href="https://acobot.ai/register?ref=wp-plugin">', '</a>' ) . '</p>';
	}
}

function acobot_settings() {
?>
	<div class='wrap'>
		  <h2><?php _e( 'Settings', 'acobot' ); ?></h2>
		  <form method='post' action='options.php'>
			<?php
		    	settings_fields( 'acobot' );
			    do_settings_sections( 'acobot' );
				
				$all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
				if (stripos(implode($all_plugins), 'woocommerce.php')) {
				    aco_wc_create_keys();
				}
				
			    submit_button();
			?>
		  </form>
	 </div>
<?php
}
