<?php
/**
 * Template part for outputting information and selections for the umbrella
 * membership.
 *
 * @author      ExchangeWP
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes, 2017 ExchangeWP.
 * @license     GPLv2
 */
?>

<?php do_action( 'it_exchange_umbrella_memberships_before_wrap' ); ?>

<div class="it-exchange-umbrella-memberships-container">

	<?php do_action( 'it_exchange_umbrella_memberships_begin_wrap' ); ?>

	<h3><?php _e( "Umbrella Membership Info", 'LION' ); ?></h3>

	<?php it_exchange( 'umbrella-membership', 'seats' ); ?>

	<?php it_exchange( 'umbrella-membership', 'members' ); ?>

	<?php do_action( 'it_exchange_umbrella_memberships_end_wrap' ); ?>

</div>

<?php do_action( 'it_exchange_umbrella_memberships_after_wrap' ); ?>
