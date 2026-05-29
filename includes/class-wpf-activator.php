<?php
/**
 * Fired during plugin activation.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Activator {

    /**
     * Set up the custom data ingestion and screening tables.
     */
    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpf_pending_review';
        $charset_collate = $wpdb->get_charset_collate();

        /**
         * Enhanced dbDelta SQL Schema Definition
         * - Added `repo_category` string mapping configuration field
         * - Exactly two spaces after PRIMARY KEY
         * - Indexed keys registered to accelerate sorting tasks
         */
        $sql = "CREATE TABLE `$table_name` (
            `id` bigint NOT NULL AUTO_INCREMENT,
            `github_repo_id` bigint NOT NULL,
            `repo_category` varchar(50) DEFAULT 'options-wrappers' NOT NULL,
            `repo_name` varchar(150) NOT NULL,
            `author_name` varchar(100) NOT NULL,
            `description` text DEFAULT '',
            `repository_url` varchar(255) NOT NULL,
            `stars` int DEFAULT 0 NOT NULL,
            `open_issues` int DEFAULT 0 NOT NULL,
            `forks` int DEFAULT 0 NOT NULL,
            `repo_status` varchar(30) DEFAULT 'pending' NOT NULL,
            `last_updated` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            `date_scraped` datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY `github_repo_id` (`github_repo_id`),
            KEY `repo_status` (`repo_status`),
            KEY `repo_category` (`repo_category`)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}