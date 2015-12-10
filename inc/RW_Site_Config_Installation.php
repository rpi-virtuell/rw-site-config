<?php

/**
 * Class RW_Site_Config_Installation
 *
 * Contains some helper code for plugin installation
 *
 */

class RW_Site_Config_Installation {
    /**
     * Check some thinks on plugin activation
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    public static function on_activate() {

        // check WordPress version
        if ( ! version_compare( $GLOBALS[ 'wp_version' ], '4.0', '>=' ) ) {
            deactivate_plugins( RW_Distributed_Profile_Server::$plugin_filename );
            die(
            wp_sprintf(
                '<strong>%s:</strong> ' .
                __( 'This plugin requires WordPress 4.0 or newer to work', RW_Site_Config::get_textdomain() )
                , RW_Site_Config::get_plugin_data( 'Name' )
            )
            );
        }


        // check php version
        if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
            deactivate_plugins( RW_Site_Config::$plugin_filename );
            die(
            wp_sprintf(
                '<strong>%1s:</strong> ' .
                __( 'This plugin requires PHP 5.3 or newer to work. Your current PHP version is %1s, please update.', RW_Site_Config::get_textdomain() )
                , RW_Site_Config::get_plugin_data( 'Name' ), PHP_VERSION
            )
            );
        }

        if ( defined( 'RW_SITE_CONFIG_PLUGINS_CONFIG' ) ) {
            if ( false === get_site_option( 'rw_site_config_plugins' ) ) {
                $plugins = unserialize( RW_SITE_CONFIG_PLUGINS_CONFIG );
                update_site_option( 'rw_site_config_plugins', $plugins );
            }
        }

        if ( defined( 'RW_SITE_CONFIG_OPTIONS_CONFIG' ) ) {
            if ( false === get_site_option( 'rw_site_config_options' ) ) {
                $options = unserialize( RW_SITE_CONFIG_OPTIONS_CONFIG );
                update_site_option( 'rw_site_config_options', $options );
            }
        }

    }

    /**
     * Clean up after deactivation
     *
     * Clean up after deactivation the plugin
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    public static function on_deactivation() {

    }

    /**
     * Clean up after uninstall
     *
     * Clean up after uninstall the plugin.
     * Delete options and other stuff.
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     *
     */
    public static function on_uninstall() {

    }
}
