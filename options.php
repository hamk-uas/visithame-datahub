<?php
/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class DataHub {
	private $datahub_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'datahub_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'datahub_page_init' ) );
	}

	public function datahub_add_plugin_page() {
		// add_menu_page(
         add_options_page(
        //add_plugins_page(
			'DataHub', // page_title
			'DataHub', // menu_title
			'manage_options', // capability
			'datahub', // menu_slug
			array( $this, 'datahub_create_admin_page' ), // function
			// 'dashicons-admin-generic', // icon_url
			// 2 // position
		);
	}

	public function datahub_create_admin_page() {
		$this->datahub_options = get_option( 'datahub_option_name' ); ?>

		<div class="wrap">
			<h2>DataHub</h2>
			<p>DataHub settings</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'datahub_option_group' );
					do_settings_sections( 'datahub-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function datahub_page_init() {
		register_setting(
			'datahub_option_group', // option_group
			'datahub_option_name', // option_name
			array( $this, 'datahub_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'datahub_setting_section', // id
			'Settings', // title
			array( $this, 'datahub_section_info' ), // callback
			'datahub-admin' // page
		);

		add_settings_field(
			'datahub_client_secret', // id
			'Client Secret', // title
			array( $this, 'client_secret_callback' ), // callback
			'datahub-admin', // page
			'datahub_setting_section' // section
		);

		add_settings_field(
			'datahub_username', // id
			'Username', // title
			array( $this, 'username_callback' ), // callback
			'datahub-admin', // page
			'datahub_setting_section' // section
		);

		add_settings_field(
			'datahub_password', // id
			'Password', // title
			array( $this, 'password_callback' ), // callback
			'datahub-admin', // page
			'datahub_setting_section' // section
		);
	}

	public function datahub_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['datahub_client_secret'] ) ) {
			$sanitary_values['datahub_client_secret'] = sanitize_text_field( $input['datahub_client_secret'] );
		}

		if ( isset( $input['datahub_username'] ) ) {
			$sanitary_values['datahub_username'] = sanitize_text_field( $input['datahub_username'] );
		}

		if ( isset( $input['datahub_password'] ) ) {
			$sanitary_values['datahub_password'] = sanitize_text_field( $input['datahub_password'] );
		}

		return $sanitary_values;
	}

	public function datahub_section_info() {
		
	}

	public function client_secret_callback() {
		printf(
			'<input class="regular-text" type="text" name="datahub_option_name[datahub_client_secret]" id="datahub_client_secret" value="%s">',
			isset( $this->datahub_options['datahub_client_secret'] ) ? esc_attr( $this->datahub_options['datahub_client_secret']) : ''
		);
	}

	public function username_callback() {
		printf(
			'<input class="regular-text" type="text" name="datahub_option_name[datahub_username]" id="datahub_username" value="%s">',
			isset( $this->datahub_options['datahub_username'] ) ? esc_attr( $this->datahub_options['datahub_username']) : ''
		);
	}

	public function password_callback() {
		printf(
			'<input class="regular-text" type="text" name="datahub_option_name[datahub_password]" id="datahub_password" value="%s">',
			isset( $this->datahub_options['datahub_password'] ) ? esc_attr( $this->datahub_options['datahub_password']) : ''
		);
	}

}
if ( is_admin() )
	$datahub = new DataHub();

/* 
 * Retrieve this value with:
 * $datahub_options = get_option( 'datahub_option_name' ); // Array of All Options
 * $client_secret = $datahub_options['datahub_client_secret']; // Client Secret
 * $username = $datahub_options['datahub_username']; // Username
 * $password = $datahub_options['datahub_password']; // Password
 */
?>