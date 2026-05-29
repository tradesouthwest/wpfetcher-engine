<?php
/**
 * The high-speed string modification and file streaming engine.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Tokenizer {

    /**
     * Compiles a master template by replacing targeted token strings.
     *
     * @param string $template_name File name inside the templates/ directory.
     * @param array  $replacements  Key-value pairs mapping placeholder tags to customized strings.
     * @return bool|string          Returns compiled content string or false on failure.
     */
    public static function compile_template( $template_name, $replacements = array() ) {
        $file_path = WPF_ENGINE_PATH . 'templates/' . sanitize_file_name( $template_name );

        if ( ! file_exists( $file_path ) ) {
            return false;
        }

        // Read template directly into string storage
        $contents = file_get_contents( $file_path );

        if ( false === $contents ) {
            return false;
        }

        // Execute zero-bloat deterministic replacements
        foreach ( $replacements as $placeholder => $value ) {
            $contents = str_replace( $placeholder, $value, $contents );
        }

        return $contents;
    }

    /**
     * Forces standard browser stream download of the newly compiled template code file.
     */
    public static function stream_file_download( $filename, $content ) {
        // Wipe any accidental buffer garbage before headers output
        if ( ob_get_length() ) {
            ob_end_clean();
        }

        // Send headers forcing standard file download stream
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/x-httpd-php; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . strlen( $content ) );

        echo $content;
        exit;
    }
}