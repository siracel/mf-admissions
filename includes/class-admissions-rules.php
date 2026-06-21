<?php
/**
 * Decision table (rules) and general settings.
 *
 * Each rule maps a gender + grade range directly to a WordPress page.
 * The result card is built from that page (title + excerpt) and the CTA
 * links to it. The decision table is stored as an option and edited from
 * the admin panel — no code changes needed to add grades or change pages.
 *
 * @package Admissions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admissions_Rules {

	const OPT_RULES    = 'admissions_rules';
	const OPT_SETTINGS = 'admissions_settings';
	const OPT_SEEDED   = 'admissions_seeded';

	/**
	 * Numeric grade value used for Preschool.
	 */
	const GRADE_PRESCHOOL = 0;

	/**
	 * Gender labels.
	 *
	 * @return array
	 */
	public static function genders() {
		return array(
			'male'   => __( 'Boys', 'admissions' ),
			'female' => __( 'Girls', 'admissions' ),
		);
	}

	/**
	 * Get the decision-table rules.
	 *
	 * @return array
	 */
	public static function get_rules() {
		$rules = get_option( self::OPT_RULES, array() );
		return is_array( $rules ) ? $rules : array();
	}

	/**
	 * Save the decision-table rules.
	 *
	 * @param array $rules Rules.
	 */
	public static function save_rules( $rules ) {
		update_option( self::OPT_RULES, array_values( (array) $rules ) );
	}

	/**
	 * Get general settings (merged with defaults).
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = array(
			'heading'       => __( 'Find the right program', 'admissions' ),
			'step1_label'   => __( 'Who is the student?', 'admissions' ),
			'step2_label'   => __( 'Select grade / age', 'admissions' ),
			'cta_label'     => __( 'Learn More', 'admissions' ),
			'contact_url'   => '',
			'contact_label' => __( 'Get in Touch', 'admissions' ),
			// Grades to display (0 = Preschool).
			'grades'        => array( 0, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ),
		);
		$saved = get_option( self::OPT_SETTINGS, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	/**
	 * Save general settings.
	 *
	 * @param array $settings Settings.
	 */
	public static function save_settings( $settings ) {
		$current = self::get_settings();
		update_option( self::OPT_SETTINGS, wp_parse_args( (array) $settings, $current ) );
	}

	/**
	 * Convert a grade value to its display label.
	 *
	 * @param int $grade Grade (0 = Preschool).
	 * @return string
	 */
	public static function grade_label( $grade ) {
		$grade = (int) $grade;
		if ( self::GRADE_PRESCHOOL === $grade ) {
			return __( 'Preschool (ages 3–5)', 'admissions' );
		}
		/* translators: %d: grade number */
		return sprintf( __( 'Grade %d', 'admissions' ), $grade );
	}

	/**
	 * Find the matching rule for a gender + grade (first match wins).
	 *
	 * @param string $gender Gender key.
	 * @param int    $grade  Grade (0 = Preschool).
	 * @return array|null Rule or null.
	 */
	public static function match( $gender, $grade ) {
		$grade = (int) $grade;
		foreach ( self::get_rules() as $rule ) {
			if ( empty( $rule['gender'] ) || $rule['gender'] !== $gender ) {
				continue;
			}
			$min = isset( $rule['min'] ) ? (int) $rule['min'] : 0;
			$max = isset( $rule['max'] ) ? (int) $rule['max'] : 0;
			if ( $grade >= $min && $grade <= $max ) {
				return $rule;
			}
		}
		return null;
	}

	/**
	 * Seed the default rules and settings (only once).
	 */
	public static function seed_defaults() {
		if ( get_option( self::OPT_SEEDED ) ) {
			return;
		}
		self::create_default_content();
	}

	/**
	 * Force a full reset: clear rules + settings and re-seed.
	 * Also removes any legacy program entries created by older versions.
	 */
	public static function reset_to_defaults() {
		// Clean up legacy program posts from older versions, if any.
		if ( class_exists( 'Admissions_Programs' ) ) {
			$legacy = get_posts(
				array(
					'post_type'   => Admissions_Programs::POST_TYPE,
					'post_status' => 'any',
					'numberposts' => -1,
					'fields'      => 'ids',
				)
			);
			foreach ( $legacy as $id ) {
				wp_delete_post( $id, true );
			}
		}

		delete_option( self::OPT_RULES );
		delete_option( self::OPT_SETTINGS );
		delete_option( self::OPT_SEEDED );

		self::create_default_content();
	}

	/**
	 * Create the default rule rows and settings.
	 *
	 * Pages are left unset (0) so the site owner assigns the destination
	 * page for each row in the Decision Table.
	 */
	private static function create_default_content() {
		$toilet = __( 'Child must be toilet-trained.', 'admissions' );
		$boarding = __( 'Full Boarding', 'admissions' );

		$rules = array(
			// Boys.
			array( 'gender' => 'male',   'min' => 0,  'max' => 0,  'page' => 0, 'badge' => __( 'Preschool', 'admissions' ),   'note' => $toilet ),
			array( 'gender' => 'male',   'min' => 3,  'max' => 4,  'page' => 0, 'badge' => __( 'Day Student', 'admissions' ), 'note' => __( '08:00 – 20:00, limited capacity.', 'admissions' ) ),
			array( 'gender' => 'male',   'min' => 5,  'max' => 12, 'page' => 0, 'badge' => $boarding, 'note' => '' ),
			// Girls.
			array( 'gender' => 'female', 'min' => 0,  'max' => 0,  'page' => 0, 'badge' => __( 'Preschool', 'admissions' ), 'note' => $toilet ),
			// Grades 3–5: no boarding for girls — leave the page empty to show the friendly "no program" card.
			array( 'gender' => 'female', 'min' => 3,  'max' => 5,  'page' => 0, 'badge' => '', 'note' => '' ),
			// Middle School (ana kampüs).
			array( 'gender' => 'female', 'min' => 6,  'max' => 8,  'page' => 0, 'badge' => $boarding, 'note' => '' ),
			// High School (Delaware şubesi).
			array( 'gender' => 'female', 'min' => 9,  'max' => 12, 'page' => 0, 'badge' => $boarding, 'note' => __( 'High School is located at our Delaware campus.', 'admissions' ) ),
		);

		self::save_rules( $rules );
		self::save_settings( array() ); // Write defaults.
		update_option( self::OPT_SEEDED, 1 );
	}
}
