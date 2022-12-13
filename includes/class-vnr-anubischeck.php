<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://extensionforge.com/
 * @since      1.0.0
 *
 * @package    Vnr_Anubischeck
 * @subpackage Vnr_Anubischeck/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Vnr_Anubischeck
 * @subpackage Vnr_Anubischeck/includes
 * @author     Steve Kraft & Peter Mertzlin <direct@extensionforge.com>
 */

function callApi()
    {	$current_user = wp_get_current_user();
    	$testEmail = $current_user->user_email;
    	$user_id = get_current_user_id();
    	
        $service = new AnubisService;

        // if user has customer number, try this first. Then try zipcode, if existant

        if (null != xprofile_get_field_data("Kundennummer", $user_id)) {
            // convert customer number to correct format if necessary
            $customerNumber = preg_replace( '/[^0-9]/', '', xprofile_get_field_data( "Kundennummer", $user_id));
            $customerNumber = substr($customerNumber, 0, 8);
         
            $hasSubscriptions = $service->hasSubscriptionsByCustomerNumber($customerNumber);
        } elseif (null != xprofile_get_field_data( "Postleitzahl", $user_id)) {
            $hasSubscriptions = $service->hasSubscriptions($testEmail, xprofile_get_field_data( "Postleitzahl", $user_id));
        } else {
            // has no customer number, has no zipcode => can't check for subscriptions
            $hasSubscriptions = false;  
        }
        // $hasSubscriptions = true;
        if ($hasSubscriptions) {
            update_user_meta( $user_id, 'bp_verified_member', "1" );	   
        } else {
            update_user_meta( $user_id, 'bp_verified_member', "" );	 
        }
    }
	
add_action('set_logged_in_cookie', 'custom_get_logged_in_cookie_anubischeck', 10, 6);
function custom_get_logged_in_cookie_anubischeck($logged_in_cookie, $expire, $expiration, $user_id, $logged_in_text, $token)
{
	callApi();
}


class My_Custom_Widget extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'my_custom_widget',
			__( 'VNR-KDNR-EINGABE', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
			'title'    => '',
			'linktitle'    => '',
			'linkurl'    => ''	
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( ' Titel', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php // Widget Link Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linktitle' ) ); ?>"><?php _e( ' Link-Titel', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linktitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linktitle' ) ); ?>" type="text" value="<?php echo esc_attr( $linktitle ); ?>" />
		</p>
		<?php // Widget Link Url ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linkurl' ) ); ?>"><?php _e( ' Link-Url', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linkurl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linkurl' ) ); ?>" type="text" value="<?php echo esc_attr( $linkurl ); ?>" />
		</p>

	

	

	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['linktitle']    = isset( $new_instance['linktitle'] ) ? wp_strip_all_tags( $new_instance['linktitle'] ) : '';
		$instance['linkurl']    = isset( $new_instance['linkurl'] ) ? wp_strip_all_tags( $new_instance['linkurl'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {

		extract( $args );

		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		
		$linktitle     = isset( $instance['linktitle'] ) ? $instance['linktitle'] : '';
		$linkurl     = isset( $instance['linkurl'] ) ? $instance['linkurl'] : '';

		//if user logged in display widget else NOT
	    if ( is_user_logged_in() ) {
 		$user_id = get_current_user_id();
    	   
	    $isvip = get_user_meta($user_id,"bp_verified_member"); 
	      
		// WordPress core before_widget hook (always include )
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text vnr_enter_customernr">';

			// Display widget title if defined

		if($isvip[0]=="1") { 

			$imageurl = plugins_url('vip-badge.jpg', __FILE__);
			//display VIP ICON
			echo '<div><h4><img style="float: left; margin-right: 10px;" src="'.$imageurl.'" alt="Club VIP Badge" border="0">Computerwissen Club</h4>
			<p>Sie sind nun angemeldet als VIP.</p></div>';

		} else {

		
		echo '<div class="vnr_enter_customernr_container"><div class="vnr_left"><input class="vnr_enter_customernr_input" type="text" id="vnr_enter_customernr"></div><div class="vnr_right"><input id="vnr_enter_customernr_submit" type="button" value="speichern" ></div></div><div style="clear:both;"></div><div id="vnr_savedkdnr"  class="vnr_saved_data"></div>';
			if ( $linktitle ) {
				echo '<div class="linkcontainer"><a class="vnr_enter_customernr_link" href="'.$linkurl.'">'.$linktitle.'</a></div>';
			}
		}
	
		
		echo '</div>';

		// WordPress core after_widget hook (always include )
		echo $after_widget;
		}

	}

}

// Register the widget
function my_register_custom_widget() {
	register_widget( 'My_Custom_Widget' );
}
add_action( 'widgets_init', 'my_register_custom_widget' );

function vnr_save_kdnr()
{
    $kdnr = $_POST['customernr'];
    $user_id = get_current_user_id();
    $user = get_user_by('id', $user_id);    
   
    xprofile_set_field_data('Kundennummer', $user_id,  $kdnr);
    echo json_encode($user_id);
}

add_action('wp_ajax_nopriv_vnr_save_kdnr', 'vnr_save_kdnr');
add_action('wp_ajax_vnr_save_kdnr', 'vnr_save_kdnr');





class Vnr_Anubischeck {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Vnr_Anubischeck_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'VNR_ANUBISCHECK_VERSION' ) ) {
			$this->version = VNR_ANUBISCHECK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'vnr-anubischeck';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Vnr_Anubischeck_Loader. Orchestrates the hooks of the plugin.
	 * - Vnr_Anubischeck_i18n. Defines internationalization functionality.
	 * - Vnr_Anubischeck_Admin. Defines all hooks for the admin area.
	 * - Vnr_Anubischeck_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-vnr-anubischeck-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/service.php';


		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-vnr-anubischeck-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-vnr-anubischeck-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-vnr-anubischeck-public.php';

		$this->loader = new Vnr_Anubischeck_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Vnr_Anubischeck_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Vnr_Anubischeck_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Vnr_Anubischeck_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Vnr_Anubischeck_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Vnr_Anubischeck_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
