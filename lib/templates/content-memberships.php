<?php
/**
 * Default template for displaying the an exchange
 * customer's profile.
 * 
 * @since 1.0.0
 * @version 1.0.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange/ directory located
 * in your theme.
*/
?>

<?php do_action( 'it_exchange_membership_addon_content_membership_before_wrap' ); ?>
<div id="it-exchange-membership-addon-membership" class="it-exchange-wrap it-exchange-account">
<?php do_action( 'it_exchange_membership_addon_content_membership_begin_wrap' ); ?>
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<?php it_exchange( 'customer', 'menu' ); ?>
	<?php it_exchange_get_template_part( 'content-memberships/loops/dashboard' ); ?>
<?php do_action( 'it_exchange_membership_addon_content_membership_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_membership_addon_content_membership_after_wrap' ); ?>