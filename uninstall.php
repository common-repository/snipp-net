<?php

function snipp_net_uninstall() {
    if (
        ! defined( 'WP_UNINSTALL_PLUGIN' ) ||
        ! WP_UNINSTALL_PLUGIN ||
        dirname( WP_UNINSTALL_PLUGIN ) !== dirname( plugin_basename( __FILE__ ) )
    ) {
        status_header( 404 );
        exit;
    }

    if ( ! defined( 'SNIPP_NET_DIR' ) ) {
        define( 'SNIPP_NET_DIR', plugin_dir_path( __FILE__ ) );
    }
    
    /**
     * Load composer dependencies
     */
    require SNIPP_NET_DIR . 'vendor/autoload.php';

    /**
     * Include the core that handles the common bits.
     */
    require_once SNIPP_NET_DIR . 'class-snipp-net-core.php';

    SnippNetCore::delete_options();
}

snipp_net_uninstall();