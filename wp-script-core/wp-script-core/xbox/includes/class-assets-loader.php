<?php namespace Xbox\Includes;

class AssetsLoader {
	public static $version;
	public static $js_loaded = false;
	public static $css_loaded = false;
	protected $xbox;
	protected $object_type;

	public function __construct( $version = '1.0.0' ) {
		self::$version = $version;

		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ), 10 );
	}

	public function load_assets( $hook ){
		self::load_google_fonts();
		self::load_scripts();
		self::load_styles();
	}

	/*
	|---------------------------------------------------------------------------------------------------
	| Google Fonts
	|---------------------------------------------------------------------------------------------------
	*/

	private static function load_google_fonts() {
		wp_enqueue_style( 'xbox-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,600i,700' );
	}

	/*
	|---------------------------------------------------------------------------------------------------
	| Add scripts
	|---------------------------------------------------------------------------------------------------
	*/
	private static function load_scripts(){
		if ( self::$js_loaded ) {
			return;
		}


		//Libs
		wp_register_script( 'xbox-spinner', XBOX_URL .'libs/spinner/spinner.min.js', array(), self::$version );
		wp_enqueue_script( 'xbox-spinner' );

		wp_register_script( 'xbox-colorpicker', XBOX_URL .'libs/tinyColorPicker/jqColorPicker.min.js', array(), self::$version );
		wp_enqueue_script( 'xbox-colorpicker' );

		wp_register_script( 'xbox-radiocheckbox', XBOX_URL .'libs/icheck/icheck.min.js', array(), self::$version );
		wp_enqueue_script( 'xbox-radiocheckbox' );

		wp_register_script( 'xbox-sui-dropdown', XBOX_URL .'libs/semantic-ui/components/dropdown.min.js', array(), self::$version );
		wp_enqueue_script( 'xbox-sui-dropdown' );

		wp_register_script( 'xbox-sui-transition', XBOX_URL .'libs/semantic-ui/components/transition.min.js', array(), self::$version );
		wp_enqueue_script( 'xbox-sui-transition' );

		wp_register_script( 'xbox-tipso', XBOX_URL .'libs/tipso/tipso.min.js', array(), self::$version );
		wp_enqueue_script( 'xbox-tipso' );

		/*wp_register_script( 'xbox-ace-editor', XBOX_URL .'libs/ace/ace.js', array(), self::$version );
		wp_enqueue_script( 'xbox-ace-editor' );*/

		wp_register_script( 'xbox-switcher', XBOX_URL .'libs/xbox-switcher/xbox-switcher.js', array(), self::$version );
		wp_enqueue_script( 'xbox-switcher' );

		wp_register_script( 'xbox-img-selector', XBOX_URL .'libs/xbox-image-selector/xbox-image-selector.js', array(), self::$version );
		wp_enqueue_script( 'xbox-img-selector' );

		wp_register_script( 'xbox-tab', XBOX_URL .'libs/xbox-tabs/xbox-tabs.js', array(), self::$version );
		wp_enqueue_script( 'xbox-tab' );

		wp_register_script( 'xbox-confirm', XBOX_URL .'libs/xbox-confirm/xbox-confirm.js', array(), self::$version );
		wp_enqueue_script( 'xbox-confirm' );


		//Wordpress scripts
		$deps_scripts = array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' );
		if ( function_exists( 'wp_enqueue_media' ) ) {
      wp_enqueue_media();
    } else {
      wp_enqueue_script( 'media-upload' );
    }
		wp_register_script( 'xbox-wpscript', XBOX_URL .'js/wpscript.js', $deps_scripts, self::$version );
		wp_enqueue_script( 'xbox-wpscript' );

		wp_localize_script('xbox-wpscript', 'xbox_ajax_var', array(
			'url'           => str_replace( array('http:', 'https:'), array('', ''), admin_url('admin-ajax.php') ),
			'nonce'         => wp_create_nonce('ajax-nonce')
    ));



		//Xbox scripts
		wp_register_script( 'xbox', XBOX_URL .'js/xbox.js', $deps_scripts, self::$version );
		wp_enqueue_script( 'xbox' );
		wp_register_script( 'xbox-events', XBOX_URL .'js/xbox-events.js', array( 'xbox' ), self::$version );
		wp_enqueue_script( 'xbox-events' );

		wp_localize_script( 'xbox', 'XBOX_JS', self::localization() );

		self::$js_loaded = true;
	}

	/*
	|---------------------------------------------------------------------------------------------------
	| Add styles
	|---------------------------------------------------------------------------------------------------
	*/
	private static function load_styles(){
		if ( self::$css_loaded ) {
			return;
		}

		wp_register_style( 'xbox-sui-icon', XBOX_URL .'libs/semantic-ui/components/icon.min.css', array(), self::$version );
		wp_enqueue_style( 'xbox-sui-icon' );

		wp_register_style( 'xbox-sui-flag', XBOX_URL .'libs/semantic-ui/components/flag.min.css', array(), self::$version );
		wp_enqueue_style( 'xbox-sui-flag' );

		wp_register_style( 'xbox-sui-dropdown', XBOX_URL .'libs/semantic-ui/components/dropdown.min.css', array(), self::$version );
		wp_enqueue_style( 'xbox-sui-dropdown' );

		wp_register_style( 'xbox-sui-transition', XBOX_URL .'libs/semantic-ui/components/transition.min.css', array(), self::$version );
		wp_enqueue_style( 'xbox-sui-transition' );

		wp_register_style( 'xbox-sui-menu', XBOX_URL .'libs/semantic-ui/components/menu.min.css', array(), self::$version );
		wp_enqueue_style( 'xbox-sui-menu' );

		wp_register_style( 'xbox-tipso', XBOX_URL .'libs/tipso/tipso.min.css', array(), self::$version );
		wp_enqueue_style( 'xbox-tipso' );

		wp_register_style( 'xbox-switcher', XBOX_URL .'libs/xbox-switcher/xbox-switcher.css', array(), self::$version );
		wp_enqueue_style( 'xbox-switcher' );

		wp_register_style( 'xbox-radiocheckbox', XBOX_URL .'libs/icheck/skins/flat/_all.css', array(), self::$version );
		wp_enqueue_style( 'xbox-radiocheckbox' );



		//Main styles
		wp_register_style( 'xbox-icons', XBOX_URL .'css/xbox-icons.css', array(), self::$version );
		wp_enqueue_style( 'xbox-icons' );

		wp_register_style( 'xbox', XBOX_URL .'css/xbox.css', array(), self::$version );
		wp_enqueue_style( 'xbox' );

		self::$css_loaded = true;
	}

	/*
	|---------------------------------------------------------------------------------------------------
	| WP Localization
	|---------------------------------------------------------------------------------------------------
	*/
	public static function localization(){
		$l10n = array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'ajax_nonce' => wp_create_nonce( 'xbox_ajax_nonce' ),
      'text' => array(
      	'popup' => array(
      		'accept_button' => _x( 'Accept', 'Button - On confirm popup', 'xbox' ),
      		'cancel_button' => _x( 'Cancel', 'Button - On confirm popup', 'xbox' ),
      	),
      	'remove_item_popup' => array(
      		'title' => _x( 'Delete', 'Title - On popup "remove item"', 'xbox' ),
      		'content' => _x( 'Are you sure you want to delete?', 'Content - On popup "remove item"', 'xbox' ),
      	),
      	'validation_url_popup' => array(
      		'title' => _x( 'Validation', 'Title - On popup "Validation url"', 'xbox' ),
      		'content' => _x( 'Please enter a valid url', 'Content - On popup "Validation url"', 'xbox' ),
      	),
      	'reset_popup' => array(
      		'title' => _x( 'Reset theme options', 'Title - On popup "Reset values"', 'xbox' ),
      		'content' => _x( 'Are you sure you want to reset all options to the default values? All saved data will be lost.', 'Content - On popup "Reset theme options"', 'xbox' ),
      	),
      	'import_popup' => array(
      		'title' => _x( 'Import theme options', 'Title - On popup "Import theme options"', 'xbox' ),
      		'content' => _x( 'Are you sure you want to import all options? All current values will be lost and will be overwritten.', 'Content - On popup "Import theme options"', 'xbox' ),
		)
      )
    );
		return $l10n;
	}


}