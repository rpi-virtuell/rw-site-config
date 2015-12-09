<?php
/**
 * RW Site Config
 *
 * @package   RW Site Config
 * @author    Frank Staude
 * @license   GPL-2.0+
 * @link      https://github.com/rpi-virtuell/rw-site-config
 */

/*
 * Plugin Name:       RW Site Config
 * Plugin URI:        https://github.com/rpi-virtuell/rw-site-config
 * Description:       A plugin to activate plugins and set optons automaticly on new sites
 * Version:           0.0.1
 * Author:            Frank Staude
 * Author URI:        https://staude.net
 * License:           GNU General Public License v2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:       /languages
 * Text Domain:       rw-site-config
 * Network:           true
 * GitHub Plugin URI: https://github.com/rpi-virtuell/rw-site-config
 * GitHub Branch:     master
 * Requires WP:       4.0
 * Requires PHP:      5.3
 */

class RW_Site_Config {
    /**
     * Plugin version
     *
     * @var     string
     * @since   0.0.1
     * @access  public
     */
    static public $version = "0.0.1";

    /**
     * Singleton object holder
     *
     * @var     mixed
     * @since   0.0.1
     * @access  private
     */
    static private $instance = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_name = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $textdomain = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_base_name = NULL;

    /**
     * @var     mixed
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_url = NULL;

    /**
     * @var     string
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_filename = __FILE__;

    /**
     * @var     string
     * @since   0.0.1
     * @access  public
     */
    static public $plugin_version = '';

    /**
     * Plugin constructor.
     *
     * @since   0.0.1
     * @access  public
     * @uses    plugin_basename
     * @action  rw_site_config_init
     */
    public function __construct () {
        // Check to prevent using this plugin not in a multisite
        if ( function_exists( 'is_multisite' ) && ! is_multisite() ) {
            add_filter( 'admin_notices', array( 'RW_Site_Config', 'error_msg_no_multisite' ) );
        }


        // set the textdomain variable
        self::$textdomain = self::get_textdomain();

        // The Plugins Name
        self::$plugin_name = $this->get_plugin_header( 'Name' );

        // The Plugins Basename
        self::$plugin_base_name = plugin_basename( __FILE__ );

        // The Plugins Version
        self::$plugin_version = $this->get_plugin_header( 'Version' );

        // Load the textdomain
        $this->load_plugin_textdomain();

        // Add Filter & Actions

        add_action( 'admin_init',           array( 'RW_Site_Config_Options', 'register_settings' ) );
        add_action( 'network_admin_menu',   array( 'RW_Site_Config_Options', 'options_menu' ) );
        add_action( 'admin_post_update_rw_site_config_settings',  array( 'RW_Site_Config_Options', 'update_settings' ) );
        add_action( 'wpmu_new_blog', array( 'RW_Site_Config_Core', 'site_created' ),10, 6 );
        add_filter( 'network_admin_plugin_action_links_' . self::$plugin_base_name, array( 'RW_Site_Config_Options', 'plugin_settings_link') );

        do_action( 'rw_site_config_init' );
    }

    /**
     * Creates an Instance of this Class
     *
     * @since   0.0.1
     * @access  public
     * @return  RW_Remote_Auth_Client
     */
    public static function get_instance() {

        if ( NULL === self::$instance )
            self::$instance = new self;

        return self::$instance;
    }

    /**
     * Load the localization
     *
     * @since	0.0.1
     * @access	public
     * @uses	load_plugin_textdomain, plugin_basename
     * @filters rw_site_config_translationpath path to translations files
     * @return	void
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( self::get_textdomain(), false, apply_filters ( 'rw_site_config_translationpath', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
    }

    /**
     * Get a value of the plugin header
     *
     * @since   0.0.1
     * @access	protected
     * @param	string $value
     * @uses	get_plugin_data, ABSPATH
     * @return	string The plugin header value
     */
    protected function get_plugin_header( $value = 'TextDomain' ) {

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php');
        }

        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }

    /**
     * get the textdomain
     *
     * @since   0.0.1
     * @static
     * @access	public
     * @return	string textdomain
     */
    public static function get_textdomain() {
        if( is_null( self::$textdomain ) )
            self::$textdomain = self::get_plugin_data( 'TextDomain' );

        return self::$textdomain;
    }

    /**
     * get the textdomain path
     *
     * @since   0.0.1
     * @static
     * @access	public
     * @return	string Domain Path
     */
    public static function get_textdomain_path() {
        return self::get_plugin_data( 'DomainPath' );
    }

    /**
     * return plugin comment data
     *
     * @since   0.0.1
     * @uses    get_plugin_data
     * @access  public
     * @param   $value string, default = 'Version'
     *		Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
     * @return  string
     */
    public static function get_plugin_data( $value = 'Version' ) {

        if ( ! function_exists( 'get_plugin_data' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        $plugin_data  = get_plugin_data ( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }

    /**
     * Display an Admin Notice if multisite is not active.
     *
     * @access  public
     * @since   0.0.1
     */
    public function error_msg_no_multisite() {

        ?>
        <div class="error">
            <p>
                <?php esc_html_e(
                    'The plugin only works in a multisite installation. See how to install a multisite network:',
                    'multisite_enhancements'
                ); ?>
                <a href="http://codex.wordpress.org/Create_A_Network" title="<?php esc_html_e(
                    'WordPress Codex: Create a network', 'multisite_enhancements'
                ); ?>">
                    <?php esc_html_e( 'WordPress Codex: Create a network', 'multisite_enhancements' ); ?>
                </a>
            </p>
        </div>
        <?php
    }
}


if ( class_exists( 'RW_Site_Config' ) ) {

    add_action( 'plugins_loaded', array( 'RW_Site_Config', 'get_instance' ) );

    require_once 'inc/RW_Site_Config_Autoloader.php';
    RW_Site_Config_Autoloader::register();

    register_activation_hook( __FILE__, array( 'RW_Site_Config_Installation', 'on_activate' ) );
    register_uninstall_hook(  __FILE__,	array( 'RW_Site_Config_Installation', 'on_uninstall' ) );
    register_deactivation_hook( __FILE__, array( 'RW_Site_Config_Installation', 'on_deactivation' ) );
}
