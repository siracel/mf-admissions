<?php
/**
 * Ön yüz Program Bulucu: shortcode, varlık yükleme ve veri aktarımı.
 *
 * @package Admissions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admissions_Finder {

	/**
	 * Veri yalnızca bir kez aktarılsın.
	 *
	 * @var bool
	 */
	private $localized = false;

	/**
	 * Kancaları bağla.
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_shortcode( 'admissions_program_finder', array( $this, 'render' ) );
		add_action( 'vc_before_init', array( $this, 'register_wpbakery' ) );
	}

	/**
	 * Varlıkları kaydet (henüz yükleme).
	 */
	public function register_assets() {
		wp_register_style(
			'admissions-finder',
			ADMISSIONS_URL . 'assets/css/admissions-finder.css',
			array(),
			ADMISSIONS_VERSION
		);
		wp_register_script(
			'admissions-finder',
			ADMISSIONS_URL . 'assets/js/admissions-finder.js',
			array(),
			ADMISSIONS_VERSION,
			true
		);
	}

	/**
	 * Ön yüze aktarılacak veri paketini hazırla.
	 *
	 * @return array
	 */
	private function build_data() {
		$settings = Admissions_Rules::get_settings();
		$rules    = Admissions_Rules::get_rules();

		// Class options.
		$grades = array();
		foreach ( (array) $settings['grades'] as $g ) {
			$grades[] = array(
				'value' => (int) $g,
				'label' => Admissions_Rules::grade_label( $g ),
			);
		}

		// Resolve each rule to its destination page (title + excerpt + image + link).
		$clean_rules = array();
		foreach ( $rules as $rule ) {
			$page_id = isset( $rule['page'] ) ? (int) $rule['page'] : 0;
			$title   = '';
			$link    = '';
			$excerpt = '';
			$image   = '';

			if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
				$title = get_the_title( $page_id );
				$link  = get_permalink( $page_id );
				if ( has_excerpt( $page_id ) ) {
					$excerpt = get_the_excerpt( $page_id );
				} else {
					$post    = get_post( $page_id );
					$excerpt = $post ? wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' ) : '';
				}
				if ( has_post_thumbnail( $page_id ) ) {
					$image = (string) get_the_post_thumbnail_url( $page_id, 'large' );
				}
			}

			$clean_rules[] = array(
				'gender'  => isset( $rule['gender'] ) ? $rule['gender'] : '',
				'min'     => isset( $rule['min'] ) ? (int) $rule['min'] : 0,
				'max'     => isset( $rule['max'] ) ? (int) $rule['max'] : 0,
				'title'   => $title,
				'excerpt' => $excerpt,
				'image'   => $image,
				'badge'   => isset( $rule['badge'] ) ? $rule['badge'] : '',
				'note'    => isset( $rule['note'] ) ? $rule['note'] : '',
				'link'    => $link ? $link : '',
			);
		}

		return array(
			'genders'  => Admissions_Rules::genders(),
			'grades'   => $grades,
			'rules'    => $clean_rules,
			'i18n'     => array(
				'noResult'      => __( 'There is currently no program available for this selection. Please get in touch and our admissions team will be happy to guide you.', 'admissions' ),
				'resultIntro'   => __( 'Recommended program', 'admissions' ),
				'noResultIntro' => __( 'Let’s talk', 'admissions' ),
			),
			'settings' => array(
				'ctaLabel'     => $settings['cta_label'],
				'contactUrl'   => $settings['contact_url'],
				'contactLabel' => $settings['contact_label'],
			),
		);
	}

	/**
	 * Shortcode çıktısı.
	 *
	 * @param array $atts Shortcode öznitelikleri.
	 * @return string
	 */
	public function render( $atts = array() ) {
		wp_enqueue_style( 'admissions-finder' );
		wp_enqueue_script( 'admissions-finder' );

		if ( ! $this->localized ) {
			wp_localize_script( 'admissions-finder', 'AdmissionsData', $this->build_data() );
			$this->localized = true;
		}

		$settings = Admissions_Rules::get_settings();
		$genders  = Admissions_Rules::genders();

		// URL parametrelerinden ön seçim (sunucu tarafı; JS ayrıca senkronize eder).
		$pre_gender = isset( $_GET['gender'] ) ? sanitize_key( wp_unslash( $_GET['gender'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$pre_grade  = isset( $_GET['grade'] ) ? sanitize_text_field( wp_unslash( $_GET['grade'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		ob_start();
		?>
		<div class="admissions-finder" data-pre-gender="<?php echo esc_attr( $pre_gender ); ?>" data-pre-grade="<?php echo esc_attr( $pre_grade ); ?>">
			<?php if ( ! empty( $settings['heading'] ) ) : ?>
				<h2 class="admissions-finder__heading"><?php echo esc_html( $settings['heading'] ); ?></h2>
			<?php endif; ?>

			<div class="admissions-finder__steps">
				<div class="admissions-finder__step" data-step="1">
					<div class="admissions-finder__step-head">
						<span class="admissions-finder__num" aria-hidden="true">01</span>
						<p class="admissions-finder__label" id="admissions-step1-label"><?php echo esc_html( $settings['step1_label'] ); ?></p>
					</div>
					<div class="admissions-finder__options" role="group" aria-labelledby="admissions-step1-label">
						<?php foreach ( $genders as $key => $label ) : ?>
							<button type="button" class="admissions-finder__btn" data-gender="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
								<span class="admissions-finder__btn-check" aria-hidden="true"></span>
								<?php echo esc_html( $label ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="admissions-finder__step" data-step="2" hidden>
					<div class="admissions-finder__step-head">
						<span class="admissions-finder__num" aria-hidden="true">02</span>
						<p class="admissions-finder__label" id="admissions-step2-label"><?php echo esc_html( $settings['step2_label'] ); ?></p>
					</div>
					<div class="admissions-finder__options admissions-finder__options--grades" role="group" aria-labelledby="admissions-step2-label">
						<?php foreach ( (array) $settings['grades'] as $g ) : ?>
							<button type="button" class="admissions-finder__btn" data-grade="<?php echo esc_attr( (int) $g ); ?>" aria-pressed="false">
								<?php echo esc_html( Admissions_Rules::grade_label( $g ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="admissions-finder__result" role="region" aria-live="polite" aria-label="<?php esc_attr_e( 'Program result', 'admissions' ); ?>" hidden></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * WPBakery (Visual Composer) öğesi olarak kaydet.
	 */
	public function register_wpbakery() {
		if ( ! function_exists( 'vc_map' ) ) {
			return;
		}
		vc_map(
			array(
				'name'        => __( 'Admissions — Program Finder', 'admissions' ),
				'base'        => 'admissions_program_finder',
				'category'    => __( 'Content', 'admissions' ),
				'icon'        => 'dashicons-welcome-learn-more',
				'description' => __( 'Gender + grade based program finder.', 'admissions' ),
				'params'      => array(),
			)
		);
	}
}
