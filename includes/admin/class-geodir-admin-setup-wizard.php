<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their directory.
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectroy/Admin
 * @version     2.0.0
 * @info        GeoDirectory Class used as a base.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Setup_Wizard class.
 */
class GeoDir_Admin_Setup_Wizard {

	/** @var string Current Step */
	private $step   = '';

	/** @var array Steps for the setup wizard */
	private $steps  = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( apply_filters( 'geodir_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'gd-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'gd-setup' !== $_GET['page'] ) {
			return;
		}
		$default_steps = array(
			'introduction' => array(
				'name'    => __( 'Introduction', 'geodirectory' ),
				'view'    => array( $this, 'setup_introduction' ),
				'handler' => '',
			),
			'maps' => array(
				'name'    => __( "Map's", 'geodirectory' ),
				'view'    => array( $this, 'setup_maps' ),
				'handler' => array( $this, 'setup_maps_save' ),
			),
			'default_location' => array(
				'name'    => __( 'Default Location', 'geodirectory' ),
				'view'    => array( $this, 'setup_default_location' ),
				'handler' => array( $this, 'setup_default_location_save' ),
			),
			'dummy_data' => array(
				'name'    => __( 'Dummy Data', 'geodirectory' ),
				'view'    => array( $this, 'setup_dummy_data' ),
				'handler' => array( $this, 'setup_dummy_data_save' ),
			),
			'next_steps' => array(
				'name'    => __( 'Ready!', 'geodirectory' ),
				'view'    => array( $this, 'setup_ready' ),
				'handler' => '',
			),
		);

		$this->steps = apply_filters( 'geodirectory_setup_wizard_steps', $default_steps );
		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$geodir_map_name = geodir_map_name();

		// load OSM styles if needed.
		if($geodir_map_name == 'osm'){
			wp_enqueue_style('geodir-leaflet-style');
		}


		// map arguments
		$map_lang = "&language=" . geodir_get_map_default_language();
		$map_key = "&key=" . geodir_get_map_api_key();
		/**
		 * Filter the variables that are added to the end of the google maps script call.
		 *
		 * This i used to change things like google maps language etc.
		 *
		 * @since 1.0.0
		 * @param string $var The string to filter, default is empty string.
		 */
		$map_extra = apply_filters('geodir_googlemap_script_extra', '');

		wp_register_script('geodir-goMap-script', geodir_plugin_url() . '/assets/js/goMap'.$suffix.'.js', array(), GEODIRECTORY_VERSION,true);
		wp_register_script('geodir-google-maps', 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra , array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-g-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-o-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms-leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-geo-script', geodir_plugin_url() . '/assets/leaflet/osm.geocode'.$suffix.'.js', array('geodir-leaflet-script'), GEODIRECTORY_VERSION);
		wp_register_script('select2', geodir_plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-admin-script', geodir_plugin_url() . '/assets/js/admin'.$suffix.'.js', array('jquery','jquery-ui-tooltip','thickbox'), GEODIRECTORY_VERSION);
		wp_add_inline_script( 'geodir-admin-script', "window.gdSetMap = window.gdSetMap || '".geodir_map_name()."';", 'before' );
		wp_add_inline_script( 'geodir-admin-script', "var ajaxurl = '".admin_url( 'admin-ajax.php' )."';", 'before' );


		wp_register_script('geodir-google-maps', 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra , array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);


		$required_scripts = array(
			'jquery',
			'jquery-ui-tooltip',
			'select2',
			'geodir-admin-script',
			'jquery-ui-progressbar',
		);

		// add maps if needed
		if (in_array($geodir_map_name, array('auto', 'google'))) {
			$required_scripts[] = 'geodir-google-maps';
			$required_scripts[] = 'geodir-g-overlappingmarker-script';
		}elseif($geodir_map_name == 'osm'){
			$required_scripts[] = 'geodir-leaflet-script';
			$required_scripts[] = 'geodir-leaflet-geo-script';
			$required_scripts[] = 'geodir-o-overlappingmarker-script';
		}
		$required_scripts[] = 'geodir-goMap-script';



		wp_register_script( 'geodir-setup', GEODIRECTORY_PLUGIN_URL . '/assets/js/setup-wizard'.$suffix.'.js', $required_scripts ,GEODIRECTORY_VERSION);


		wp_localize_script('geodir-setup', 'geodir_params', geodir_params());

		wp_enqueue_style('font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), GEODIRECTORY_VERSION);
		wp_enqueue_style('geodir-admin-css', geodir_plugin_url() . '/assets/css/admin.css', array(), GEODIRECTORY_VERSION);
		wp_enqueue_style('geodir-jquery-ui-css', geodir_plugin_url() . '/assets/css/jquery-ui.css', array(), GEODIRECTORY_VERSION);
		wp_enqueue_style('jquery-ui-core');
		wp_enqueue_style( 'geodir-setup-wizard', GEODIRECTORY_PLUGIN_URL . '/assets/css/setup-wizard.css', array( 'dashicons', 'install','thickbox' ), GEODIRECTORY_VERSION );
		wp_enqueue_style( 'select2', GEODIRECTORY_PLUGIN_URL . '/assets/css/select2/select2.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-leaflet-style', geodir_plugin_url() . '/assets/leaflet/leaflet.css', array(), GEODIRECTORY_VERSION);

		// load OSM styles if needed.
		if($geodir_map_name == 'osm'){
			wp_enqueue_style('geodir-leaflet-style', geodir_plugin_url() . '/assets/leaflet/leaflet.css', array(), GEODIRECTORY_VERSION);
		}

		

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}



		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 * @param string step   slug (default: current step)
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 3.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ] );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'GeoDirectory &rsaquo; Setup Wizard', 'geodirectory' ); ?></title>
			<?php wp_print_scripts( 'geodir-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="gd-setup wp-core-ui">
			<h1 id="gd-logo"><a href="https://wpgeodirectory.com/"><img src="<?php echo GEODIRECTORY_PLUGIN_URL; ?>/assets/images/gd-logo-grey.png" alt="GeoDirectory" /></a></h1>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<?php if ( 'next_steps' === $this->step ) : ?>
				<p class="gd-return-to-dashboard-wrap"><a class="gd-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'geodirectory' ); ?></a></p>
			<?php endif; ?>
			</body>
		</html>
		<?php
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$ouput_steps = $this->steps;
		array_shift( $ouput_steps );
		?>
		<ol class="gd-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="<?php
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
					}
				?>"><?php echo esc_html( $step['name'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="gd-setup-content">';
		call_user_func( $this->steps[ $this->step ]['view'], $this );
		echo '</div>';
	}

	/**
	 * Introduction step.
	 */
	public function setup_introduction() {
		?>
		<h1><?php esc_html_e( 'Welcome to the world of GeoDirectory!', 'geodirectory' ); ?></h1>
		<p><?php _e( 'Thank you for choosing GeoDirectory to power your online directory! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>', 'geodirectory' ); ?></p>
		<p><?php esc_html_e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'geodirectory' ); ?></p>
		<p class="gd-setup-actions step">
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s go!', 'geodirectory' ); ?></a>
			<a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php esc_html_e( 'Not right now', 'geodirectory' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Setup maps api.
	 */
	public function setup_maps() {
		?>
		<form method="post">
			<p><?php esc_html_e( 'To get maps to work properly in your directory please fill out the below details.', 'geodirectory' );  ?></p>


			<table class="gd-setup-maps" cellspacing="0">

				<tbody>

				<?php
				$settings = array();
				$settings[] = GeoDir_Settings_General::get_maps_api_setting();
				$settings[] = GeoDir_Settings_General::get_map_language_setting();
				$api_arr = GeoDir_Settings_General::get_google_maps_api_key_setting();
				// change the tooltip description/
				$api_arr['desc'] = __( 'This is a requirement to use Google Maps. If you would prefer to use the Open Street Maps API, set the Maps API to OSM.', 'geodirectory' );

				$settings[] = $api_arr ;

				GeoDir_Admin_Settings::output_fields($settings);
				?>


				</tbody>
			</table>

			<p><?php esc_html_e( '( The Google maps API key is essential unless you are using OSM or no maps )', 'geodirectory' ); ?></p>

			<p class="gd-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save Maps Settings.
	 */
	public function setup_maps_save() {
		check_admin_referer( 'gd-setup' );

		$settings = array();
		$settings[] = GeoDir_Settings_General::get_maps_api_setting();
		$settings[] = GeoDir_Settings_General::get_map_language_setting();
		$settings[]  = GeoDir_Settings_General::get_google_maps_api_key_setting();

		GeoDir_Admin_Settings::save_fields( $settings );
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Default Location settings.
	 */
	public function setup_default_location() {

		$this->google_maps_api_check();
		?>

		<form method="post">
				<?php
				$generalSettings = new GeoDir_Settings_General();
				$settings = $generalSettings->get_settings('location');

				// Change the description
				$settings[0]['title'] = '';
				$settings[0]['desc'] = __( 'Drag the map or the marker to set the city/town you wish to use as the default location.', 'geodirectory' );
				GeoDir_Admin_Settings::output_fields($settings);
				?>
			<p class="gd-setup-actions step">
				<?php $generalSettings->output_toggle_advanced(); ?>
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>

		<?php
	}

	/**
	 * Shows an error message if there is a problem with the google maps api key settings.
	 */
	public function google_maps_api_check(){
		//maps_api

		$maps_api = geodir_get_option('maps_api');
		if($maps_api=='auto' || $maps_api=='google'){
			$maps_api_key = geodir_get_option('google_maps_api_key');
			if(empty($maps_api_key)){
				$message = esc_html__( 'You have not set a Google Maps API key, please press the back button in your browser and add a key.', 'geodirectory' );
			}else{
				$message = esc_html__( 'There is a problem with the Google Maps API key you have set, please press the back button in your browser and add a valid key.', 'geodirectory' );
			}

		?>
		<p class="gd-google-api-error" style="display: none;">
			<?php echo '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> '.$message; ?>
		</p>
		<script>
			function gm_authFailure(){ jQuery('.gd-google-api-error').show(); }
		</script>
		<?php
		}
	}

	/**
	 * Save Default Location Settings.
	 */
	public function setup_default_location_save() {
		check_admin_referer( 'gd-setup' );

		$generalSettings = new GeoDir_Settings_General();
		$settings = $generalSettings->get_settings('location');
		GeoDir_Admin_Settings::save_fields( $settings );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Dummy Data setup.
	 */
	public function setup_dummy_data() {
		?>
		<form method="post">

				<?php
				//$settings = array();

				$generalSettings = new GeoDir_Settings_General();
				$settings = $generalSettings->get_settings('dummy_data');

				// Change the description
				$settings[0]['title'] = '';
				//$settings[0]['desc'] = __( 'Drag the map or the marker to set the city/town you wish to use as the default location.', 'geodirectory' );
				GeoDir_Admin_Settings::output_fields($settings);
				?>

			<p class="gd-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Dummy data save.
	 *
	 * This is done via ajax so we just pass onto the next step.
	 */
	public function setup_dummy_data_save() {
		check_admin_referer( 'gd-setup' );
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Final step.
	 */
	public function setup_ready() {
		$this->setup_ready_actions();
		?>

		<h1><?php esc_html_e( 'Awesome, your directory is ready!', 'geodirectory' ); ?></h1>

		<?php if ( 'unknown' === get_option( 'geodirectory_allow_tracking', 'unknown' ) ) : ?>
			<div class="geodirectory-message geodirectory-tracker">
				<p><?php printf( __( 'Want to help make GeoDirectory even more awesome? Allow GeoDirectory to collect non-sensitive diagnostic data and usage information. %1$sFind out more%2$s.', 'geodirectory' ), '<a href="https://wpgeodirectory.com/usage-tracking/" target="_blank">', '</a>' ); ?></p>
				<p class="submit">
					<a class="button-primary button button-large" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd_tracker_optin', 'true' ), 'gd_tracker_optin', 'gd_tracker_nonce' ) ); ?>"><?php esc_html_e( 'Allow', 'geodirectory' ); ?></a>
					<a class="button-secondary button button-large skip"  href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd_tracker_optout', 'true' ), 'gd_tracker_optout', 'gd_tracker_nonce' ) ); ?>"><?php esc_html_e( 'No thanks', 'geodirectory' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<div class="gd-setup-next-steps">
			<div class="gd-setup-next-steps-first">
				<h2><?php esc_html_e( 'Next steps', 'geodirectory' ); ?></h2>
				<ul>
					<li class="setup-listing"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=gd_place' ) ); ?>"><?php esc_html_e( 'Create your first listing!', 'geodirectory' ); ?></a></li>
				</ul>
			</div>
			<div class="gd-setup-next-steps-last">
				<h2><?php _e( 'Learn more', 'geodirectory' ); ?></h2>
				<ul>
					<li class="gd-getting-started"><a href="https://wpgeodirectory.com/docs/category/getting-started/?utm_source=setupwizard&utm_medium=product&utm_content=getting-started&utm_campaign=geodirectoryplugin"><?php esc_html_e( 'Getting started guide', 'geodirectory' ); ?></a></li>
					<li class="gd-newsletter"><a href="https://wpgeodirectory.com/newsletter-signup/?utm_source=setupwizard&utm_medium=product&utm_content=newsletter&utm_campaign=geodirectoryplugin"><?php esc_html_e( 'Get GeoDirectory advice in your inbox', 'geodirectory' ); ?></a></li>
					<li class="gd-get-help"><a href="https://wpgeodirectory.com/support/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=geodirectoryplugin"><?php esc_html_e( 'Have questions? Get help.', 'geodirectory' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Actions on the final step.
	 */
	private function setup_ready_actions() {
		GeoDir_Admin_Notices::remove_notice( 'install' );

		if ( isset( $_GET['gd_tracker_optin'] ) && isset( $_GET['gd_tracker_nonce'] ) && wp_verify_nonce( $_GET['gd_tracker_nonce'], 'gd_tracker_optin' ) ) {
			geodir_update_option( 'usage_tracking', true );
			GeoDir_Admin_Tracker::send_tracking_data( true );

		} elseif ( isset( $_GET['gd_tracker_optout'] ) && isset( $_GET['gd_tracker_nonce'] ) && wp_verify_nonce( $_GET['gd_tracker_nonce'], 'gd_tracker_optout' ) ) {
			geodir_update_option( 'usage_tracking', false);
		}
	}
}

new GeoDir_Admin_Setup_Wizard();