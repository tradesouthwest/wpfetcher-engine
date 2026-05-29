<?php
/**
 * Catches public frontend form configurations and maps them to the tokenizer.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Receiver {

    /**
     * Bind our request listeners to ClassicPress/WordPress core loops.
     */
    public static function init() {
        // The action names here must precisely match the POST "action" hidden field
        add_action( 'wp_ajax_wpf_generate_boilerplate', array( __CLASS__, 'handle_generation_request' ) );
        add_action( 'wp_ajax_nopriv_wpf_generate_boilerplate', array( __CLASS__, 'handle_generation_request' ) );
    }

    /**
     * Intercepts, cleans, and executes compilation queries.
     */
    public static function handle_generation_request() {
        // Check our custom security token to drop malicious script bots
        if ( ! isset( $_POST['wpf_token'] ) || ! wp_verify_nonce( $_POST['wpf_token'], 'wpf_frontend_download' ) ) {
            wp_die( 'Security check failed. Please refresh your directory workspace and try again.', 403 );
        }

        // Validate that we have received a designated target blueprint file name
        if ( empty( $_POST['template_file'] ) ) {
            wp_die( 'Error: No valid target boilerplate template specified.', 400 );
        }

        // Extract and scrub the developer parameters
        $custom_name   = ! empty( $_POST['plugin_name'] )   ? sanitize_text_field( $_POST['plugin_name'] )   : 'Custom Extension';
        $custom_prefix = ! empty( $_POST['plugin_prefix'] ) ? sanitize_text_field( $_POST['plugin_prefix'] ) : 'Wpf_Custom';
        $text_domain   = ! empty( $_POST['text_domain'] )   ? sanitize_text_field( $_POST['text_domain'] )   : 'wpf-custom';
        $target_file   = sanitize_file_name( $_POST['template_file'] );

        // Ensure the class prefix is a valid PHP identifier string (letters, numbers, underscores only)
        $custom_prefix = preg_replace( '/[^A-Za-z0-9_]/', '', $custom_prefix );
        
        // Ensure the text domain complies with slug standardization profiles
        $text_domain   = sanitize_title( $text_domain );

        // Build our replacement variable dictionary array
        $string_mappings = array(
            '{{CUSTOM_NAME}}'    => $custom_name,
            '{{CUSTOM_PREFIX}}'  => $custom_prefix,
            '{{TEXT_DOMAIN}}'    => $text_domain,
        );

        // Access the tokenization class utilities safely with explicit extension assignment
        require_once WPF_ENGINE_PATH . 'includes/class-wpf-tokenizer.php';
        $compiled_output = Wpf_Tokenizer::compile_template( $target_file, $string_mappings );

        if ( ! $compiled_output ) {
            wp_die( 'System Error: The requested boilerplate asset could not be read or compiled.', 500 );
        }

        // Establish an instantaneous output download naming slug format
        $download_filename = $text_domain . '.php';

        // Stream the completed, customized boilerplate package straight back to the user's browser
        Wpf_Tokenizer::stream_file_download( $download_filename, $compiled_output );
    }
}