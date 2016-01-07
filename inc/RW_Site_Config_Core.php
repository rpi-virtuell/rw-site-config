<?php

/**
 * Class RW_Site_Config_Core
 *
 * Contains core functions for site config plugin
 *
 */

class RW_Site_Config_Core {

    /**
     * Write config for the new blog.
     *
     * @param int    $blog_id Blog ID.
     * @param int    $user_id User ID.
     * @param string $domain  Site domain.
     * @param string $path    Site path.
     * @param int    $site_id Site ID. Only relevant on multi-network installs.
     * @param array  $meta    Meta data. Used to set initial site options.
     *
     * @todo  catch errors on activate_plugin and add_option
     */
    function site_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        $active_plugins = get_site_option( 'rw_site_config_plugins' );
        $options = get_site_option( 'rw_site_config_options' );
        switch_to_blog( $blog_id );
        foreach( $active_plugins as $plugin => $value ) {
            $result = activate_plugin( $plugin, NULL, false, true );
        }
        foreach ($options as $key => $value ) {
            add_option( $key, unserialize( stripslashes( $value ) ) );
        }
        restore_current_blog();
        return;
    }
}
