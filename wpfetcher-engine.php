<?php
/**
 * Plugin Name:       WPFetcher Engine
 * Plugin URI:        https://wpfetcher.com
 * Description:       The core ingestion, metadata processing, filtering, and tokenization engine for WPFetcher.com.
 * Version:           1.0.0
 * Author:            WPFetcher Team
 * License:           GPL-2.0+
 * Requires at least: ClassicPress 1.0.0 / WordPress 5.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define Core Constants
 */
define( 'WPF_ENGINE_VERSION', '1.0.0' );
define( 'WPF_ENGINE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wpfetcher_engine() {
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-activator.php';
    Wpf_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wpfetcher_engine() {
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-deactivator.php';
    Wpf_Deactivator::deactivate();
}

// Register lifecycle hooks
register_activation_hook( __FILE__, 'activate_wpfetcher_engine' );
register_deactivation_hook( __FILE__, 'deactivate_wpfetcher_engine' );

/**
 * Initialize Engine Modules
 */
function run_wpfetcher_engine() {
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-scraper.php';
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-tokenizer.php';
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-exporter.php';
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-receiver.php';
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-router.php';
    require_once WPF_ENGINE_PATH . 'includes/class-wpf-compiler.php';
    
    // This MUST fire globally Static Factory / Static Initialization pattern
    Wpf_Receiver::init(); 
    Wpf_Router::init();
    Wpf_Engine_Compiler::init();
    if ( is_admin() ) {
        // In your core loader file:
        require_once WPF_ENGINE_PATH . 'admin/class-wpf-admin.php';
        Wpf_Admin::init();
    }
}
run_wpfetcher_engine();