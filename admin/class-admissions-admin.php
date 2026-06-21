<?php
/**
 * Admin UI: decision table (rules) and general settings.
 *
 * @package Admissions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admissions_Admin {

	const PAGE        = 'admissions-settings';
	const NONCE       = 'admissions_settings_save';
	const RESET_NONCE = 'admissions_reset_defaults';

	/**
	 * Bind hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_admissions_save_settings', array( $this, 'handle_save' ) );
		add_action( 'admin_post_admissions_reset_defaults', array( $this, 'handle_reset' ) );
		add_filter( 'plugin_action_links_' . ADMISSIONS_BASENAME, array( $this, 'action_links' ) );
	}

	/**
	 * Add the top-level menu page.
	 */
	public function add_menu() {
		add_menu_page(
			__( 'Admissions — Program Finder', 'admissions' ),
			__( 'Admissions', 'admissions' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' ),
			'dashicons-welcome-learn-more',
			26
		);
	}

	/**
	 * Add a "Settings" link to the plugins list.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public function action_links( $links ) {
		$url  = admin_url( 'admin.php?page=' . self::PAGE );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'admissions' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = Admissions_Rules::get_settings();
		$rules    = Admissions_Rules::get_rules();
		$genders  = Admissions_Rules::genders();
		$pages    = get_pages( array( 'sort_column' => 'menu_order,post_title' ) );
		$saved    = isset( $_GET['updated'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$reset    = isset( $_GET['reset'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$reset_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=admissions_reset_defaults' ),
			self::RESET_NONCE
		);
		?>
		<div class="wrap admissions-admin">
			<style>
				.admissions-admin{--ad-green:#1c5d4c;--ad-green-050:#eef3f1;--ad-line:#e2e8e5;max-width:920px;}
				.admissions-admin h1{font-size:1.6rem;}
				.admissions-admin__masthead{display:flex;align-items:center;gap:14px;background:var(--ad-green);color:#fff;border-radius:12px;padding:18px 22px;margin:14px 0 22px;}
				.admissions-admin__masthead .dashicons{font-size:30px;width:30px;height:30px;}
				.admissions-admin__masthead .admissions-admin__title{color:#fff;margin:0;font-size:1.35rem;line-height:1.2;font-weight:600;}
				.admissions-admin__masthead p{margin:2px 0 0;color:rgba(255,255,255,.82);font-size:13px;}
				.admissions-admin .wp-header-end{display:none;margin:0;border:0;height:0;}
				.admissions-admin__masthead-actions{margin-left:auto;}
				.admissions-admin__masthead-actions a{color:#fff;font-size:12px;opacity:.85;text-decoration:underline;}
				.admissions-admin__masthead-actions a:hover{opacity:1;color:#fff;}
				.admissions-admin__shortcode{display:flex;align-items:center;gap:10px;background:var(--ad-green-050);border:1px solid var(--ad-line);border-radius:10px;padding:12px 16px;margin:0 0 24px;}
				.admissions-admin__shortcode code{background:#fff;border:1px solid var(--ad-line);border-radius:6px;padding:4px 10px;font-size:13px;color:var(--ad-green);font-weight:600;}
				.admissions-card{background:#fff;border:1px solid var(--ad-line);border-radius:12px;padding:6px 24px 22px;margin:0 0 22px;box-shadow:0 1px 2px rgba(20,40,34,.04);}
				.admissions-card__title{display:flex;align-items:baseline;gap:10px;border-bottom:1px solid var(--ad-line);padding:18px 0 14px;margin:0 0 18px;}
				.admissions-card__title h2{margin:0;font-size:1.15rem;}
				.admissions-card__title .admissions-step{font-size:11px;font-weight:700;letter-spacing:.08em;color:var(--ad-green);background:var(--ad-green-050);border-radius:999px;padding:3px 9px;text-transform:uppercase;}
				.admissions-card .description{margin:0 0 16px;}
				#admissions-rules-table{border-radius:8px;overflow:hidden;}
				#admissions-rules-table th{font-weight:600;}
				#admissions-rules-table td{vertical-align:middle;}
				.admissions-admin .admissions-remove-rule{color:#b32d2e;}
				.admissions-admin .form-table th{padding-left:0;}
				.admissions-admin__save{position:sticky;bottom:0;background:linear-gradient(180deg,rgba(255,255,255,0),#fff 40%);padding:14px 0;margin-top:4px;}
				.admissions-admin__save .button-primary{background:var(--ad-green);border-color:var(--ad-green);}
				.admissions-admin__save .button-primary:hover{background:#16493b;border-color:#16493b;}
			</style>

			<div class="admissions-admin__masthead">
				<span class="dashicons dashicons-welcome-learn-more"></span>
				<div>
					<div class="admissions-admin__title"><?php esc_html_e( 'Admissions — Program Finder', 'admissions' ); ?></div>
					<p><?php esc_html_e( 'Decision table and display settings', 'admissions' ); ?></p>
				</div>
				<div class="admissions-admin__masthead-actions">
					<a href="<?php echo esc_url( $reset_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'This will reset all rules and settings to the defaults. Continue?', 'admissions' ) ); ?>');">
						<?php esc_html_e( 'Restore defaults', 'admissions' ); ?>
					</a>
				</div>
			</div>

			<?php // Real heading for accessibility + the anchor WordPress uses to place admin notices (keeps them out of the green masthead). ?>
			<h1 class="screen-reader-text"><?php esc_html_e( 'Admissions — Program Finder', 'admissions' ); ?></h1>
			<hr class="wp-header-end" />

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'admissions' ); ?></p></div>
			<?php endif; ?>
			<?php if ( $reset ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Rules and settings restored to defaults.', 'admissions' ); ?></p></div>
			<?php endif; ?>

			<div class="admissions-admin__shortcode">
				<span><?php esc_html_e( 'Shortcode to embed on a page:', 'admissions' ); ?></span>
				<code>[admissions_program_finder]</code>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="admissions_save_settings" />
				<?php wp_nonce_field( self::NONCE, self::NONCE ); ?>

				<div class="admissions-card">
					<div class="admissions-card__title">
						<span class="admissions-step">01</span>
						<h2><?php esc_html_e( 'Decision Table', 'admissions' ); ?></h2>
					</div>
					<p class="description">
						<?php esc_html_e( 'For each gender + grade range, pick the page the visitor should be sent to. First matching rule wins, so keep narrow ranges on top. Grade value 0 = Preschool. Leave the page empty to show the friendly “no program available” card. Label and Note are optional and appear on the result card.', 'admissions' ); ?>
					</p>

					<table class="widefat striped" id="admissions-rules-table">
						<thead>
							<tr>
								<th style="width:13%"><?php esc_html_e( 'Gender', 'admissions' ); ?></th>
								<th style="width:8%"><?php esc_html_e( 'Min.', 'admissions' ); ?></th>
								<th style="width:8%"><?php esc_html_e( 'Max.', 'admissions' ); ?></th>
								<th style="width:23%"><?php esc_html_e( 'Destination Page', 'admissions' ); ?></th>
								<th style="width:15%"><?php esc_html_e( 'Label (badge)', 'admissions' ); ?></th>
								<th><?php esc_html_e( 'Note', 'admissions' ); ?></th>
								<th style="width:6%"></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( empty( $rules ) ) {
								$rules = array( array( 'gender' => 'male', 'min' => 0, 'max' => 0, 'page' => 0 ) );
							}
							foreach ( $rules as $i => $rule ) :
								$this->render_rule_row( $i, $rule, $genders, $pages );
							endforeach;
							?>
						</tbody>
					</table>
					<p><button type="button" class="button" id="admissions-add-rule"><?php esc_html_e( '+ Add Rule', 'admissions' ); ?></button></p>
				</div><!-- /.admissions-card -->

				<div class="admissions-card">
					<div class="admissions-card__title">
						<span class="admissions-step">02</span>
						<h2><?php esc_html_e( 'Appearance & Text', 'admissions' ); ?></h2>
					</div>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="af-heading"><?php esc_html_e( 'Heading', 'admissions' ); ?></label></th>
							<td><input type="text" class="regular-text" id="af-heading" name="settings[heading]" value="<?php echo esc_attr( $settings['heading'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="af-step1"><?php esc_html_e( 'Step 1 Label', 'admissions' ); ?></label></th>
							<td><input type="text" class="regular-text" id="af-step1" name="settings[step1_label]" value="<?php echo esc_attr( $settings['step1_label'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="af-step2"><?php esc_html_e( 'Step 2 Label', 'admissions' ); ?></label></th>
							<td><input type="text" class="regular-text" id="af-step2" name="settings[step2_label]" value="<?php echo esc_attr( $settings['step2_label'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="af-cta"><?php esc_html_e( 'CTA Button Text', 'admissions' ); ?></label></th>
							<td><input type="text" class="regular-text" id="af-cta" name="settings[cta_label]" value="<?php echo esc_attr( $settings['cta_label'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="af-contact-url"><?php esc_html_e( 'Contact Link (URL)', 'admissions' ); ?></label></th>
							<td>
								<input type="text" class="regular-text" id="af-contact-url" name="settings[contact_url]" value="<?php echo esc_attr( $settings['contact_url'] ); ?>" placeholder="https://" />
								<p class="description"><?php esc_html_e( 'When set, a secondary “Get in Touch” button is shown. It is also the fallback when a rule has no page yet.', 'admissions' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="af-contact-label"><?php esc_html_e( 'Contact Button Text', 'admissions' ); ?></label></th>
							<td><input type="text" class="regular-text" id="af-contact-label" name="settings[contact_label]" value="<?php echo esc_attr( $settings['contact_label'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="af-grades"><?php esc_html_e( 'Grades to Show', 'admissions' ); ?></label></th>
							<td>
								<input type="text" class="regular-text" id="af-grades" name="settings[grades]" value="<?php echo esc_attr( implode( ', ', (array) $settings['grades'] ) ); ?>" />
								<p class="description"><?php esc_html_e( 'Comma-separated. 0 = Preschool. e.g. 0, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12', 'admissions' ); ?></p>
							</td>
						</tr>
					</table>
				</div><!-- /.admissions-card -->

				<div class="admissions-admin__save">
					<?php submit_button( __( 'Save Changes', 'admissions' ), 'primary', 'submit', false ); ?>
				</div>
			</form>
		</div>

		<script>
		( function () {
			var table = document.getElementById( 'admissions-rules-table' );
			var addBtn = document.getElementById( 'admissions-add-rule' );
			if ( ! table || ! addBtn ) { return; }
			var tbody = table.querySelector( 'tbody' );

			function reindex() {
				var rows = tbody.querySelectorAll( 'tr' );
				rows.forEach( function ( row, idx ) {
					row.querySelectorAll( '[name]' ).forEach( function ( field ) {
						field.name = field.name.replace( /rules\[\d+\]/, 'rules[' + idx + ']' );
					} );
				} );
			}

			addBtn.addEventListener( 'click', function () {
				var first = tbody.querySelector( 'tr' );
				if ( ! first ) { return; }
				var clone = first.cloneNode( true );
				tbody.appendChild( clone );
				reindex();
			} );

			tbody.addEventListener( 'click', function ( e ) {
				if ( e.target.classList.contains( 'admissions-remove-rule' ) ) {
					if ( tbody.querySelectorAll( 'tr' ).length > 1 ) {
						e.target.closest( 'tr' ).remove();
						reindex();
					}
				}
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * Render a single rule row.
	 *
	 * @param int   $i       Row index.
	 * @param array $rule    Rule.
	 * @param array $genders Genders.
	 * @param array $pages   Site pages.
	 */
	private function render_rule_row( $i, $rule, $genders, $pages = array() ) {
		$gender = isset( $rule['gender'] ) ? $rule['gender'] : 'male';
		$min    = isset( $rule['min'] ) ? (int) $rule['min'] : 0;
		$max    = isset( $rule['max'] ) ? (int) $rule['max'] : 0;
		$page   = isset( $rule['page'] ) ? (int) $rule['page'] : 0;
		$badge  = isset( $rule['badge'] ) ? $rule['badge'] : '';
		$note   = isset( $rule['note'] ) ? $rule['note'] : '';
		?>
		<tr>
			<td>
				<select name="rules[<?php echo (int) $i; ?>][gender]">
					<?php foreach ( $genders as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $gender, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><input type="number" name="rules[<?php echo (int) $i; ?>][min]" value="<?php echo esc_attr( $min ); ?>" min="0" max="12" style="width:60px" /></td>
			<td><input type="number" name="rules[<?php echo (int) $i; ?>][max]" value="<?php echo esc_attr( $max ); ?>" min="0" max="12" style="width:60px" /></td>
			<td>
				<select name="rules[<?php echo (int) $i; ?>][page]" style="width:100%">
					<option value="0"><?php esc_html_e( '— Select a page —', 'admissions' ); ?></option>
					<?php foreach ( $pages as $pg ) : ?>
						<option value="<?php echo (int) $pg->ID; ?>" <?php selected( $page, $pg->ID ); ?>><?php echo esc_html( $pg->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><input type="text" name="rules[<?php echo (int) $i; ?>][badge]" value="<?php echo esc_attr( $badge ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'e.g. Full Boarding', 'admissions' ); ?>" /></td>
			<td><input type="text" name="rules[<?php echo (int) $i; ?>][note]" value="<?php echo esc_attr( $note ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'e.g. High School is at our Delaware campus.', 'admissions' ); ?>" /></td>
			<td><button type="button" class="button-link delete admissions-remove-rule"><?php esc_html_e( 'Remove', 'admissions' ); ?></button></td>
		</tr>
		<?php
	}

	/**
	 * Handle the save action.
	 */
	public function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'admissions' ) );
		}
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			wp_die( esc_html__( 'Security check failed.', 'admissions' ) );
		}

		// Rules.
		$rules_in = isset( $_POST['rules'] ) && is_array( $_POST['rules'] ) ? wp_unslash( $_POST['rules'] ) : array();
		$rules    = array();
		foreach ( $rules_in as $row ) {
			$gender = isset( $row['gender'] ) ? sanitize_key( $row['gender'] ) : 'male';
			if ( ! array_key_exists( $gender, Admissions_Rules::genders() ) ) {
				$gender = 'male';
			}
			$rules[] = array(
				'gender' => $gender,
				'min'    => isset( $row['min'] ) ? max( 0, min( 12, (int) $row['min'] ) ) : 0,
				'max'    => isset( $row['max'] ) ? max( 0, min( 12, (int) $row['max'] ) ) : 0,
				'page'   => isset( $row['page'] ) ? absint( $row['page'] ) : 0,
				'badge'  => isset( $row['badge'] ) ? sanitize_text_field( $row['badge'] ) : '',
				'note'   => isset( $row['note'] ) ? sanitize_text_field( $row['note'] ) : '',
			);
		}
		Admissions_Rules::save_rules( $rules );

		// General settings.
		$settings_in = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		$grades_raw  = isset( $settings_in['grades'] ) ? $settings_in['grades'] : '';
		$grades      = array();
		foreach ( explode( ',', $grades_raw ) as $g ) {
			$g = trim( $g );
			if ( '' === $g ) {
				continue;
			}
			$grades[] = max( 0, min( 12, (int) $g ) );
		}
		$grades = array_values( array_unique( $grades ) );

		$settings = array(
			'heading'       => isset( $settings_in['heading'] ) ? sanitize_text_field( $settings_in['heading'] ) : '',
			'step1_label'   => isset( $settings_in['step1_label'] ) ? sanitize_text_field( $settings_in['step1_label'] ) : '',
			'step2_label'   => isset( $settings_in['step2_label'] ) ? sanitize_text_field( $settings_in['step2_label'] ) : '',
			'cta_label'     => isset( $settings_in['cta_label'] ) ? sanitize_text_field( $settings_in['cta_label'] ) : '',
			'contact_url'   => isset( $settings_in['contact_url'] ) ? esc_url_raw( $settings_in['contact_url'] ) : '',
			'contact_label' => isset( $settings_in['contact_label'] ) ? sanitize_text_field( $settings_in['contact_label'] ) : '',
			'grades'        => ! empty( $grades ) ? $grades : array( 0, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ),
		);
		Admissions_Rules::save_settings( $settings );

		$this->redirect_back( 'updated' );
	}

	/**
	 * Handle the "restore defaults" action.
	 */
	public function handle_reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'admissions' ) );
		}
		check_admin_referer( self::RESET_NONCE );

		Admissions_Rules::reset_to_defaults();
		$this->redirect_back( 'reset' );
	}

	/**
	 * Redirect back to the settings page with a status flag.
	 *
	 * @param string $flag Query flag (updated|reset).
	 */
	private function redirect_back( $flag ) {
		$redirect = add_query_arg(
			array(
				'page' => self::PAGE,
				$flag  => 1,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}
}
