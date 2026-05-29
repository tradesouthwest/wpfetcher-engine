<?php
/**
 * Intercepts specific page layout parameters to load views directly from the plugin.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Router {

    /**
     * Hook intercept rules into standard core loading phases.
     */
    public static function init() {
        add_filter( 'template_include', array( __CLASS__, 'route_marketplace_view' ) );
    }

    /**
     * Re-routes standard page parsing configurations to point to local plugin code assets.
     */
    public static function route_marketplace_view( $template ) {
        // Target specifically when a user views a single page entry
        if ( is_page() ) {
            global $post;

            // Define the precise targeted page slug key pattern matching your checkout layout
            if ( isset( $post->post_name ) && 'marketplace-directory' === $post->post_name ) {
                $plugin_template = WPF_ENGINE_PATH . 'frontend/views-directory.php';

                if ( file_exists( $plugin_template ) ) {
                    return $plugin_template; // Force-inject the plugin file path completely dropping theme fallbacks
                }
            }
        }

        return $template;
    }
}