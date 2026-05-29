<?php
/**
 * Asynchronous Generation and File Tokenization Engine Compiler.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Engine_Compiler {

    /**
     * Public Static Initializer Engine.
     * Aligns completely with core system design orchestration patterns.
     */
    public static function init() {
        // Wire up the AJAX action handler routing vectors statically
        add_action( 'wp_ajax_wpf_generate_boilerplate', array( __CLASS__, 'process_boilerplate_generation' ) );
        add_action( 'wp_ajax_nopriv_wpf_generate_boilerplate', array( __CLASS__, 'process_boilerplate_generation' ) );
    }

    /**
     * Intercept form payloads, parse formatting rules, replace tokens, and stream downloads.
     * Note: Changed to a public static method to match the static execution route.
     */
    public static function process_boilerplate_generation() {
        // 1. Enforce strict cryptographic sanity boundaries
        if ( ! isset( $_POST['wpf_token'] ) || ! wp_verify_nonce( $_POST['wpf_token'], 'wpf_frontend_download' ) ) {
            wp_die( esc_html__( 'Security validation mismatch. Aborting execution pipeline.', 'wpfetcher-engine' ), 403 );
        }

        // 2. Extract and sanitize parameters safely
        $raw_plugin_name   = isset( $_POST['plugin_name'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_name'] ) ) : '';
        $raw_plugin_prefix = isset( $_POST['plugin_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_prefix'] ) ) : '';
        $raw_text_domain   = isset( $_POST['text_domain'] ) ? sanitize_key( wp_unslash( $_POST['text_domain'] ) ) : '';
        $target_template   = isset( $_POST['template_file'] ) ? sanitize_file_name( wp_unslash( $_POST['template_file'] ) ) : '';

        if ( empty( $raw_plugin_name ) || empty( $raw_plugin_prefix ) || empty( $raw_text_domain ) || empty( $target_template ) ) {
            wp_die( esc_html__( 'Required parameters are missing. Execution terminated.', 'wpfetcher-engine' ), 400 );
        }

        // 3. Resolve template path locations
        $plugin_root_dir = plugin_dir_path( dirname( __FILE__ ) );
        $source_file_path = $plugin_root_dir . 'templates/' . $target_template;

        if ( ! file_exists( $source_file_path ) ) {
            wp_die( esc_html__( 'The requested base blueprint asset could not be located.', 'wpfetcher-engine' ), 404 );
        }

        // 4. Ingest file cleanly
        $file_contents = file_get_contents( $source_file_path );
        if ( false === $file_contents ) {
            wp_die( esc_html__( 'Internal I/O failure while streaming layout lines.', 'wpfetcher-engine' ), 500 );
        }

        // 5. Replace structural placeholder tokens
        $token_map = array(
            '{{PLUGIN_NAME}}'    => $raw_plugin_name,
            '{{CUSTOM_PREFIX}}'  => $raw_plugin_prefix,
            '{{TEXT_DOMAIN}}'    => $raw_text_domain,
            '{{CLASS_PREFIX}}'   => $raw_plugin_prefix,
            '{{slug}}'           => $raw_text_domain
        );

        $compiled_output = str_replace( array_keys( $token_map ), array_values( $token_map ), $file_contents );

        // 6. Calculate file nomenclature headers
        $download_name = str_replace( array( '.txt', '.php.txt' ), '.php', $target_template );
        if ( '.php' !== substr( $download_name, -4 ) ) {
            $download_name .= '.php';
        }

        // 7. Stream parsed raw output block to the browser download buffer
        if ( ob_get_level() ) {
            ob_end_clean();
        }

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/x-httpd-php; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . esc_attr( $download_name ) . '"' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . strlen( $compiled_output ) );

        echo $compiled_output;
        exit;
    }
}