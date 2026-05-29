<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wpf_Deactivator {

    /**
     * Clean up short-lived runtime artifacts without destroying persistent user tables.
     */
    public static function deactivate() {
        // Clear scheduled crons or clear active caching transients here if necessary.
        // We purposefully leave the custom table intact during simple deactivations 
        // to protect collected dataset records.
    }
}