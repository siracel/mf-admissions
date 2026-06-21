<?php
/**
 * Plugin Name:       Admissions — İsabet Academy Program Finder
 * Plugin URI:        https://mfdsgn.com/
 * Description:       A gender + grade based "Program Finder" for the Admissions page. The decision table is edited from the CMS; programs are managed as a separate content type.
 * Version:           1.1.0
 * Author:            MF
 * Author URI:        https://mfdsgn.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       admissions
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package Admissions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Doğrudan erişimi engelle.
}

define( 'ADMISSIONS_VERSION', '1.1.0' );
define( 'ADMISSIONS_FILE', __FILE__ );
define( 'ADMISSIONS_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADMISSIONS_URL', plugin_dir_url( __FILE__ ) );
define( 'ADMISSIONS_BASENAME', plugin_basename( __FILE__ ) );

require_once ADMISSIONS_DIR . 'includes/class-admissions.php';
require_once ADMISSIONS_DIR . 'includes/class-admissions-rules.php';
require_once ADMISSIONS_DIR . 'includes/class-admissions-finder.php';

if ( is_admin() ) {
	require_once ADMISSIONS_DIR . 'admin/class-admissions-admin.php';
}

/**
 * Aktivasyon: varsayılan karar tablosunu ve ayarları tohumla, rewrite kurallarını temizle.
 */
function admissions_activate() {
	Admissions_Rules::seed_defaults();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'admissions_activate' );

/**
 * Deaktivasyon: rewrite kurallarını temizle (veriyi silmez).
 */
function admissions_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'admissions_deactivate' );

/**
 * Çalıştır.
 */
function admissions_run() {
	$plugin = Admissions::instance();
	$plugin->init();
}
add_action( 'plugins_loaded', 'admissions_run' );
