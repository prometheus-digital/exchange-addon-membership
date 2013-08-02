<?php
/**
 * The following file contains utility functions specific to our membership add-on
 * If you're building your own product-type addon, it's likely that you will
 * need to do similar things. This includes enqueueing scripts, formatting data for stripe, etc.
*/

/**
 * Adds actions to the plugins page for the iThemes Exchange Membership plugin
 *
 * @since 1.0.0
 *
 * @param array $meta Existing meta
 * @param string $plugin_file the wp plugin slug (path)
 * @param array $plugin_data the data WP harvested from the plugin header
 * @param string $context 
 * @return array
*/
function it_exchange_membership_plugin_row_actions( $actions, $plugin_file, $plugin_data, $context ) {
    
    $actions['setup_addon'] = '<a href="' . get_admin_url( NULL, 'admin.php?page=it-exchange-addons&add-on-settings=membership' ) . '">' . __( 'Setup Add-on', 'LION' ) . '</a>';
    
    return $actions;
    
}
add_filter( 'plugin_action_links_exchange-addon-membership/exchange-addon-membership.php', 'it_exchange_membership_plugin_row_actions', 10, 4 );