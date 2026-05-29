<?php
/**
 * Handles the Vetting Queue workspace in the admin dashboard.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Admin {

    /**
     * Initialize admin hooks.
     */
    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'process_queue_actions' ) );
        add_action( 'admin_init', array( __CLASS__, 'process_manual_fetch_action' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_vetting_menu' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_manual_fetch_menu' ) );
    }

    /**
     * Creates the custom desk-bound menu option.
     */
    public static function register_vetting_menu() {
        add_menu_page(
            'WPFetcher Vetting Queue',
            'WPFetcher Queue',
            'manage_options',
            'wpf-vetting-queue',
            array( __CLASS__, 'render_queue_page' ),
            'dashicons-download',
            26
        );
    }
    public static function register_manual_fetch_menu() {
        add_submenu_page(
            'wpf-vetting-queue', // Parent slug
            'Manual Scraper',    // Page title
            'Manual Scraper',    // Menu title
            'manage_options',
            'wpf-manual-fetch',
            array( __CLASS__, 'render_manual_fetch_page' )
        );
    }
    public static function render_manual_fetch_page() {
        // Check if the current user has the capacity to run this
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        // Display feedback notices if a run was just executed
        if ( isset( $_GET['wpf_fetch_status'] ) && $_GET['wpf_fetch_status'] === 'success' ) {
            $count = isset( $_GET['scraped'] ) ? intval( $_GET['scraped'] ) : 0;
            echo '<div class="updated notice is-dismissible"><p>' . sprintf( __( 'Success! Ingestion completed. Scraped %d items.' ), $count ) . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'WPFetcher Ingestion Controls', 'wpfetcher-engine' ); ?></h1>
            <div class="card" style="max-width: 600px; padding: 20px;">
                <p><?php _e( 'Manually trigger a real-time ingestion run against the GitHub REST API.', 'wpfetcher-engine' ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wpf-manual-fetch' ) ); ?>">

                    <?php wp_nonce_field( 'wpf_manual_fetch_action', 'wpf_manual_fetch_nonce' ); ?>
                    <input type="hidden" name="wpf_admin_action" value="trigger_manual_fetch" />
                    
                    <p>
                    <label style="display:block; margin-bottom:8px; font-weight:bold;" for="wpf_fetch_category">
                        <?php _e( 'Select Target Category Boilerplate:', 'wpfetcher' ); ?>
                    </label>
                    <select name="wpf_fetch_category" id="wpf_fetch_category" style="width:100%; max-width:400px; height:32px;">
                        <option value="settings_api"><?php _e( 'WP Settings API Wrappers', 'wpfetcher-enigine' ); ?></option>
                        <option value="options_class"><?php _e( 'ClassicPress Options Classes', 'wpfetcher-enigine' ); ?></option>
                        <option value="cpt_generator"><?php _e( 'Custom Post Type Boilerplates', 'wpfetcher-enigine' ); ?></option>
                        <option value="widget_basic"><?php _e( 'Dashboard Widgets Templates', 'wpfetcher-enigine' ); ?></option>
                    </select>
                    </p>

                    <?php submit_button( __( 'Fetch & Process Repositories', 'wpfetcher-enigine' ), 'primary', 'submit_fetch' ); ?>
                    
                </form>
            </div>
            <?php
            if ( isset( $_POST['wpf_trigger_fetch'] ) && check_admin_referer( 'wpf_manual_fetch', 'wpf_fetch_nonce' ) ) {
                // Here you will call your future Wpf_Scraper::discover() method
                echo '<div class="notice notice-success"><p>Fetch initiated...</p></div>';
            }
    }

    /**
     * Catch and process the manual fetch form submission
     */
    public static function process_manual_fetch_action() {
        // 1. Verify our specific action context exists
        if ( ! isset( $_POST['wpf_admin_action'] ) || $_POST['wpf_admin_action'] !== 'trigger_manual_fetch' ) {
            return;
        }

        // 2. Enforce strict permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Security check failed: Insufficient permissions.', 'wpfetcher' ) );
        }

        // 3. Check the Nonce key to ensure authenticity
        if ( ! isset( $_POST['wpf_manual_fetch_nonce'] ) || ! wp_verify_nonce( $_POST['wpf_manual_fetch_nonce'], 'wpf_manual_fetch_action' ) ) {
            wp_die( __( 'Security check failed: Invalid nonce token.', 'wpfetcher' ) );
        }
        // Capture and sanitize the selected category slug from the form post
        // Default fallbacks if data is missing
        $keyword  = 'wp settings api class';
        $category = 'options-wrappers';

        // Parse out our grouped selection values
        if ( ! empty( $_POST['wpf_target_preset'] ) ) {
            $raw_preset = sanitize_text_field( $_POST['wpf_target_preset'] );
            $parts      = explode( '|', $raw_preset );
            
            if ( count( $parts ) === 2 ) {
                $keyword  = trim( $parts[0] );
                $category = trim( $parts[1] );
            }
        }
        $scraped_count = 0;

            if ( class_exists( 'Wpf_Scraper' ) ) {
                // Correctly calls the fixed static function inside includes/class-wpf-scraper.php
                $results = Wpf_Scraper::discover_repositories( $keyword, $category ); 
                
                // Extract the positive counts from your summary stats payload
                if ( isset( $results['saved_to_db'] ) ) {
                    $scraped_count = intval( $results['saved_to_db'] );
                }
            }

            wp_redirect( admin_url( 'admin.php?page=wpf-manual-fetch&wpf_fetch_status=success&scraped=' . $scraped_count ) );
            exit;
    }

    /**
     * Processes row state changes when an asset is manually approved or rejected.
     */
    public static function process_queue_actions() {
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'wpf-vetting-queue' ) {
            return;
        }

        if ( isset( $_GET['wpf_action'] ) && isset( $_GET['id'] ) ) {
            // Verify security nonce to protect against CSRF attacks
            check_admin_referer( 'wpf_queue_action_nonce' );

            global $wpdb;
            $table_name = $wpdb->prefix . 'wpf_pending_review';
            $record_id  = (int) $_GET['id'];
            $new_status = sanitize_text_field( $_GET['wpf_action'] );

            if ( in_array( $new_status, array( 'approved', 'rejected' ), true ) ) {
                $wpdb->update(
                    $table_name,
                    array( 'repo_status' => $new_status ),
                    array( 'id' => $record_id ),
                    array( '%s' ),
                    array( '%d' )
                );

                /**
                 * 🚀 AUTOMATED STATIC COMPILER
                 * Regenerate the flat library file on every state shift
                 */
                require_once WPF_ENGINE_PATH . 'includes/class-wpf-exporter.php';
                Wpf_Exporter::regenerate_static_library();

                wp_safe_redirect( admin_url( 'admin.php?page=wpf-vetting-queue' ) );
                exit;
            }
        }
    }

    /**
     * Renders the HTML workspace display.
     */
    public static function render_queue_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpf_pending_review';

        // Retrieve only entries that are currently sitting in our pending workspace
        $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE repo_status = 'pending' ORDER BY stars DESC" );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">WPFetcher Review Queue</h1>
            <p>Review incoming repositories that successfully passed your automated metrics filter. Approve code assets to advance them into active tokenization templates.</p>
            
            <table class="wp-list-table widefat fixed striped table-view-list" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 20%;">Repository Asset</th>
                        <th style="width: 15%;">Category</th> <th style="width: 35%;">Description</th>
                        <th style="width: 15%; text-align: center;">Metrics</th>
                        <th style="width: 15%; text-align: right;">Vetting Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $results ) ) : ?>
                        <tr>
                            <td colspan="5">Excellent work! The vetting queue is entirely empty. Run a new scrape search sequence to populate your dashboard workspace.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $results as $row ) : ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url( $row->repository_url ); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php echo esc_html( $row->author_name . ' / ' . $row->repo_name ); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge" style="background:#e2e8f0; color:#334155; padding:3px 8px; border-radius:4px; font-family:monospace; font-size:11px;">
                                        <?php echo esc_html( $row->repo_category ); ?>
                                    </span>
                                </td>
                                <td>
                                    <p style="margin: 0; font-size: 13px;">
                                        <?php echo ! empty( $row->description ) ? esc_html( $row->description ) : '<em>No metadata description provided by author.</em>'; ?>
                                    </p>
                                </td>
                                <td style="text-align: center;">
                                    <span class="dashicons dashicons-star-filled" style="color:#ffb900; font-size:16px; width:16px; height:16px;"></span> <?php echo (int) $row->stars; ?> Stars &nbsp;|&nbsp;
                                    <span class="dashicons dashicons-warning" style="font-size:16px; width:16px; height:16px;"></span> <?php echo (int) $row->open_issues; ?> Issues
                                </td>
                                <td style="text-align: right; vertical-align: middle;">
                                    <?php 
                                    $approve_url = wp_nonce_url( admin_url( 'admin.php?page=wpf-vetting-queue&wpf_action=approved&id=' . $row->id ), 'wpf_queue_action_nonce' );
                                    $reject_url  = wp_nonce_url( admin_url( 'admin.php?page=wpf-vetting-queue&wpf_action=rejected&id=' . $row->id ), 'wpf_queue_action_nonce' );
                                    ?>
                                    <a href="<?php echo $approve_url; ?>" class="button button-primary button-small" style="background:#46b450; border-color:#3ca047;">Approve</a>
                                    <a href="<?php echo $reject_url; ?>" class="button button-link-delete button-small" style="margin-left:8px; text-decoration:none;">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}