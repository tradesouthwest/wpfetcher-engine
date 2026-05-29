<?php
/**
 * Handles GitHub REST API Ingestion and Automated Filtering.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Scraper {

    /**
     * Executes an API ingestion cycle against GitHub and tags items by category.
     *
     * @param string $keyword  Search phrase to execute against the GitHub API.
     * @param string $category Core architectural category bucket mapping slug descriptor.
     * @return array           Summary statistics of items processed, saved, and filtered.
     */
    public static function discover_repositories( $keyword = 'wp settings api class', $category = 'options-wrappers' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpf_pending_review';

        $url = 'https://api.github.com/search/repositories?q=' . urlencode( $keyword . ' language:php' ) . '&sort=updated&order=desc';

        $args = array(
            'timeout'    => 15,
            'user-agent' => 'WPFetcher-Engine/1.0.0 (https://wpfetcher.com)',
            'headers'    => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
        );

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            return array( 'error' => $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( empty( $data ) || ! isset( $data['items'] ) ) {
            return array( 'error' => 'Invalid data payload received or rate-limit hit.' );
        }

        $stats = array(
            'total_scraped'   => count( $data['items'] ),
            'saved_to_queue'  => 0,
            'filtered_out'    => 0,
        );

        $min_stars   = 1;
        $max_issues  = 15;
        $cutoff_date = strtotime( '-24 months' );

        foreach ( $data['items'] as $item ) {
            $repo_id      = (int) $item['id'];
            $stars        = (int) $item['stargazers_count'];
            $open_issues  = (int) $item['open_issues_count'];
            $forks        = (int) $item['forks_count'];
            $last_updated = strtotime( $item['updated_at'] );

            if ( $stars < $min_stars || $open_issues > $max_issues || $last_updated < $cutoff_date ) {
                $stats['filtered_out']++;
                continue;
            }

            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE github_repo_id = %d",
                $repo_id
            ) );

            if ( $exists > 0 ) {
                continue; 
            }

            $mysql_updated_at = date( 'Y-m-d H:i:s', $last_updated );

            $inserted = $wpdb->insert(
                $table_name,
                array(
                    'github_repo_id' => $repo_id,
                    'repo_category'  => sanitize_text_field( $category ), // <-- Saved securely here
                    'repo_name'      => sanitize_text_field( $item['name'] ),
                    'author_name'    => sanitize_text_field( $item['owner']['login'] ),
                    'description'    => sanitize_textarea_field( $item['description'] ),
                    'repository_url' => esc_url_raw( $item['html_url'] ),
                    'stars'          => $stars,
                    'open_issues'    => $open_issues,
                    'forks'          => $forks,
                    'repo_status'    => 'pending',
                    'last_updated'   => $mysql_updated_at,
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
            );

            if ( $inserted ) {
                $stats['saved_to_queue']++;
            }
        }

        return $stats;
    }
}