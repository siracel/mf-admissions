<?php
/**
 * Çekirdek eklenti sınıfı.
 *
 * @package Admissions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admissions {

	/**
	 * Tekil örnek.
	 *
	 * @var Admissions|null
	 */
	private static $instance = null;

	/**
	 * Örneği al.
	 *
	 * @return Admissions
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Doğrudan örneklemeyi engelle.
	 */
	private function __construct() {}

	/**
	 * Bileşenleri başlat.
	 */
	public function init() {
		load_plugin_textdomain( 'admissions', false, dirname( ADMISSIONS_BASENAME ) . '/languages' );

		// Front-end Program Finder (shortcode + assets + data).
		$finder = new Admissions_Finder();
		$finder->hooks();

		// Admin UI.
		if ( is_admin() ) {
			$admin = new Admissions_Admin();
			$admin->hooks();
		}
	}
}
