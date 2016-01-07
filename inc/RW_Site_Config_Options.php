<?php

/**
 * Class RW_Site_Config_Options
 *
 * Contains some helper code for plugin options
 *
 */

class RW_Site_Config_Options {
    /**
     * Register all settings
     *
     * Register all the settings, the plugin uses.
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function register_settings() {
        register_setting( 'rw_site_config_options', 'rw_site_config_plugins' );
    }

    /**
     * Add a settings link to the  pluginlist
     *
     * @since   0.1
     * @access  public
     * @static
     * @param   string array links under the pluginlist
     * @return  array
     */
    static public function plugin_settings_link( $links ) {
        $settings_page = is_multisite() ? 'settings.php' : 'options-general.php';
        $settings_link = '<a href="' . network_admin_url( $settings_page ) . '?page=' . RW_Site_Config::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Generate the options menu page
     *
     * Generate the options page under the options menu
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function options_menu() {
        if ( is_multisite() ) {
            add_submenu_page(
                'settings.php',
                __('Site Config Settings', 'rw_site_config'),
                __('Site Config', 'rw_site_config-updater'),
                'manage_network_options',
                RW_Site_Config::$plugin_base_name,
                array( 'RW_Site_Config_Options', 'create_options' )
            );
        }
    }

    /**
     * Generate the options page for the plugin
     *
     * @since   0.1
     * @access  public
     * @static
     *
     * @return  void
     */
    static public function create_options() {
        $active_plugins = get_site_option( 'rw_site_config_plugins' );
        ?>
        <div class="wrap"  id="rwsiteconfigptions">
            <h2><?php _e( 'Site Config Options', RW_Site_Config::$textdomain ); ?></h2>
            <p><?php _e( 'Settings for Site Config', RW_Site_Config::$textdomain ); ?></p>
            <form method="POST" action="<?php echo admin_url('admin-post.php?action=update_rw_site_config_settings'); ?>"><fieldset class="widefat">
                    <?php wp_nonce_field('update_rw_site_config_settings'); ?>
                    <h3><?php _e( 'Pluginlist', RW_Site_Config::$textdomain ); ?></h3>
                    <p><?php _e( 'Plugins to activate on new sites.', RW_Site_Config::$textdomain ); ?></p>

                    <?php
                    $plugins = get_plugins();
                    foreach( $plugins as $path => $plugin ) {
                        $fieldname = 'rw_site_config_plugins[' . $path . ']';
                        ?>
                        <label for="<?php echo $fieldname; ?>">
                         <input id="<?php echo $fieldname; ?>" type="checkbox" value="1" <?php if ( isset( $active_plugins[ $path  ] ) &&  $active_plugins[ $path] == 1 ) echo " checked "; ?>   name="<?php echo $fieldname; ?>">
                        <?php echo $plugin[ 'Title' ] . "<br/>"?></label>
                        <?php
                        }
                    ?>
                    <br/>
                    <h3><?php _e( 'Options', RW_Site_Config::$textdomain ); ?></h3>
                    <br/>
                    <?php
                    $options = get_site_option( 'rw_site_config_options' );
                    ?>

                    <table>
                        <tr>
                            <th><?php _e( 'delete', RW_Site_Config::$textdomain );?></th>
                            <th><?php _e( 'Key', RW_Site_Config::$textdomain );?></th>
                            <th><?php _e( 'Value', RW_Site_Config::$textdomain );?></th>
                        </tr>
                    <?php
                    foreach( $options as $key => $value ) { ?>
                        <tr>
                            <td><input type="checkbox" name="rw_site_config_delete[<?php echo $key; ?>]"></td>
                            <td><?php echo $key; ?></td>
                            <td><?php echo $value; ?></td>
                        </tr>
                    <?php } ?>
                    </table>
                    <br/>
                    <h3><?php _e( 'Add new option', RW_Site_Config::$textdomain ); ?></h3>

                    <label for="rw_site_config_add_key">
                        <?php _e( 'Add new option', RW_Site_Config::$textdomain ); ?><br/>
                        <input type="text" id="rw_site_config_add_key" name="rw_site_config_add_key" class="large-text">
                    </label>
                    <br/>
                    <label for="rw_site_config_add_value">
                        <?php _e( 'Add new value', RW_Site_Config::$textdomain ); ?><br/>
                        <textarea id="rw_site_config_add_value" name="rw_site_config_add_value" rows="5" cols="45" class="large-text"></textarea>
                    </label>
                    <br/>

                    <input type="submit" class="button-primary" value="<?php _e('Save Changes' )?>" />
            </form>
            <br/>
            <br/>

            <h3><?php _e( 'Config for wp-config.php on new mu-sites', RW_Site_Config::$textdomain ); ?></h3>
            <div>
                <textarea class="large-text">
define('RW_SITE_CONFIG_PLUGINS_CONFIG','<?php echo serialize( $active_plugins ); ?>');
define('RW_SITE_CONFIG_OPTIONS_CONFIG','<?php echo serialize( $options ); ?>');
                </textarea>

            </div>
        </div>
        <?php
    }

    function update_settings() {
        check_admin_referer( 'update_rw_site_config_settings' );
        if( !current_user_can( 'manage_network_options' ) ) wp_die( 'FU');

        if( isset( $_POST[ 'rw_site_config_plugins' ] ) ) {
            if ( is_array( $_POST[ 'rw_site_config_plugins' ] ) ) {
                update_site_option( 'rw_site_config_plugins', ( $_POST[ 'rw_site_config_plugins' ] ) );
            }
        }

        if ( isset ( $_POST[ 'rw_site_config_delete' ] )) {
            $options = get_site_option( 'rw_site_config_options' );
            foreach( $_POST[ 'rw_site_config_delete' ] as $key => $value ) {
                unset ( $options[ $key ] );
            }
            update_site_option( 'rw_site_config_options', ( $options ) );
        }
        if ( isset ( $_POST[ 'rw_site_config_add_value' ] )  and isset ( $_POST[ 'rw_site_config_add_key' ] ) ) {
            $options = get_site_option( 'rw_site_config_options' );
            if ( ! is_array( $options) ) $options = array();
            $options[ ( $_POST[ 'rw_site_config_add_key' ] ) ] = ( $_POST[ 'rw_site_config_add_value' ] );
            update_site_option( 'rw_site_config_options', ( $options ) );
        }
        wp_redirect( admin_url( 'network/settings.php?page=' . RW_Site_Config::$plugin_base_name ) );
        exit;
    }
}