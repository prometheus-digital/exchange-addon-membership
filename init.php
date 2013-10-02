<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
*/

/**
 * New API functions.
*/
include( 'api/load.php' );
	
/**
 * The following file contains utility functions specific to our membership add-on
 * If you're building your own addon, it's likely that you will
 * need to do similar things.
*/
include( 'lib/addon-functions.php' );

/**
 * Exchange Add-ons require several hooks in order to work properly. 
 * We've placed them all in one file to help add-on devs identify them more easily
*/
include( 'lib/required-hooks.php' );

/**
 * We decided to place all AJAX hooked functions into this file, just for ease of use
*/
include( 'lib/addon-ajax-hooks.php' );
	
/**
 * The following file adds a new metabox are to non-iThemes Exchange posttypes
*/
include( 'lib/addon-metaboxes.php' );

/**
 * Custom integrations.
*/
include( 'lib/integrations/builder/init.php' );

/**
 * New Product Features added by the Exchange Membership Add-on.
*/
require( 'lib/product-features/load.php' );