<?php
/**
 * The default template part for the membership content in
 * the content-memberships template part's content elements
 *
 * @since 1.0.0
 * @version 1.0.0
 * @package IT_Exchange_Addon_Membership
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-memberships/elements/ directory
 * located in your theme.
*/
?>
<?php if ( it_exchange( 'member-dashboard', 'supports-membership-content' ) ) : ?>
	<?php do_action( 'it_exchange_membership_addon_content_memberships_before_membership_content_element' ); ?>
	<div class="it-exchange-membership-membership-content it-exchange-advanced-item">
		<p><?php it_exchange( 'member-dashboard', 'membership-content' ); ?></p>
	</div>
	<?php do_action( 'it_exchange_membership_addon_content_memberships_after_membership_content_element' ); ?>
<?php endif; ?>