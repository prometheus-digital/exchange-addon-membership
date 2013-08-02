<?php
/**
 * Includes all of our product features
 * @since 0.4.0
 * @package IT_Exchange
*/

// Product Feature: Title
require( ITE_MEMBERSHIP_PLUGIN_PATH . 'lib/product-features/class.access-level.php' );

// Product Feature: Base Price
require( ITE_MEMBERSHIP_PLUGIN_PATH . 'lib/product-features/class.duration.php' );

// Product Feature: Product Images 
require( ITE_MEMBERSHIP_PLUGIN_PATH . 'lib/product-features/class.status.php' );

// Product Feature: Downloads
require( ITE_MEMBERSHIP_PLUGIN_PATH . 'lib/product-features/class.success-page.php' );

// Product Feature: Product Description
require( ITE_MEMBERSHIP_PLUGIN_PATH . 'lib/product-features/class.trial-period.php' );
