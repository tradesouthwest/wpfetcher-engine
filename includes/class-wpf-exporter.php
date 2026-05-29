<?php
/**
 * Handles compiling database metrics into a static public JSON array file.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Exporter {

    /**
     * Queries all approved asset records and writes them out to a flat file.
     *
     * @return bool True on success, false on write failure.
     */
    public static function regenerate_static_library() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpf_pending_review';

        $rows = $wpdb->get_results( "SELECT * FROM $table_name WHERE repo_status = 'approved' ORDER BY stars DESC" );

        $library_payload = array();

        foreach ( $rows as $row ) {
            
            // Match the blueprint filename target bounds based on row category classification
            $template_file = 'sample-settings-wrapper.txt'; 

            if ( 'cpt-registrars' === $row->repo_category ) {
                $template_file = 'sample-cpt-registrar.txt';
            } elseif ( 'http-clients' === $row->repo_category ) {
                $template_file = 'sample-http-client.txt'; // <-- Dynamic mapping added here
            }

            $library_payload[] = array(
                'id'             => (int) $row->id,
                'slug'           => sanitize_title( $row->repo_name ),
                'name'           => esc_html( $row->repo_name ),
                'category'       => esc_html( $row->repo_category ),
                'author'         => esc_html( $row->author_name ),
                'description'    => esc_html( $row->description ),
                'url'            => esc_url_raw( $row->repository_url ),
                'stars'          => (int) $row->stars,
                'forks'          => (int) $row->forks,
                'last_updated'   => esc_html( $row->last_updated ),
                'template_file'  => $template_file, 
            );
        }

        $json_data = json_encode( $library_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        $destination_path = WPF_ENGINE_PATH . 'library.json';

        require_once ABSPATH . 'wp-admin/includes/file.php';
        global $wp_filesystem;
        WP_Filesystem();

        if ( $wp_filesystem ) {
            return $wp_filesystem->put_contents( $destination_path, $json_data, FS_CHMOD_FILE );
        }

        return false;
    }
}