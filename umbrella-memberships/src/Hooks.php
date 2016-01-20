<?php
/**
 * Main plugin hooks.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace ITEGMS;

use ITEGMS\Purchase\Purchase;
use ITEGMS\Purchase\Purchase_Query;
use ITEGMS\Relationship\Relationship_Query;
use ITEGMS\Relationship\Relationship;

/**
 * Class Hooks
 *
 * @package ITEGMS
 */
class Hooks {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_filter( 'it_exchange_multi_item_product_allowed', array(
			__CLASS__,
			'allow_multi_item_product_if_umbrella_membership'
		), 15, 2 );

		add_filter( 'it_exchange_get_stripe_make_payment_button', array(
			__CLASS__,
			'check_for_umbrella_membership'
		), 5 );

		add_filter( 'it_exchange_get_stripe_make_payment_button', array(
			__CLASS__,
			'remove_umbrella_membership_filters'
		), 15 );

		add_filter( 'it_exchange_stripe_addon_subscription_args', array(
			__CLASS__,
			'add_quantity_arg_to_stripe'
		) );

		add_action( 'it_exchange_add_transaction_success', array(
			__CLASS__,
			'record_umbrella_membership_purchase'
		) );

		add_action( 'it_exchange_membership_addon_content_memberships_start_content_loop', array(
			__CLASS__,
			'display_membership_payer_information'
		) );

		add_filter( 'it_exchange_possible_template_paths', array(
			__CLASS__,
			'add_template_path'
		) );

		add_filter( 'it_exchange_get_content_memberships_available_content_elements', array(
			__CLASS__,
			'add_child_member_selection_to_membership_page'
		) );

		add_action( 'wp_enqueue_scripts', array(
			__CLASS__,
			'enqueue_scripts'
		) );

		add_action( 'init', array(
			__CLASS__,
			'save_members_list'
		) );

		add_filter( 'get_user_metadata', array(
			__CLASS__,
			'override_member_access'
		), 10, 4 );

		add_filter( 'update_user_metadata', array(
			__CLASS__,
			'remove_added_member_access'
		), 10, 5 );

		add_action( 'it_exchange_recurring_payments_addon_update_transaction_subscriber_status', array(
			__CLASS__,
			'handle_expiry_on_subscriber_status_change'
		), 10, 2 );

		add_action( 'it_exchange_update_transaction_status', array(
			__CLASS__,
			'handle_expiry_on_transaction_change'
		), 10, 4 );

		add_action( 'itegms_activate_purchase', array(
			__CLASS__,
			'transfer_relationships_on_activation'
		) );

		add_action( 'itegms_deactivate_purchase', array(
			__CLASS__,
			'transfer_relationships_on_expiry'
		) );

		add_action( 'itegms_create_purchase', array(
			__CLASS__,
			'move_expired_relationships_on_new_purchase'
		) );

		add_action( 'delete_user', array(
			__CLASS__,
			'delete_relationships_on_user_deletion'
		) );

		add_action( 'delete_user', array(
			__CLASS__,
			'delete_customer_purchases_on_user_deletion'
		) );

		add_action( 'before_delete_post', array(
			__CLASS__,
			'delete_purchases_on_product_deletion'
		) );

		add_action( 'before_delete_post', array(
			__CLASS__,
			'delete_purchases_on_transaction_deletion'
		) );
	}

	/**
	 * Get the cart product.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	private static function get_cart_product() {

		$products = it_exchange_get_cart_products();

		// when checking out with a membership product in the cart
		// multi-item cart is disabled so grab the first product

		return reset( $products );
	}

	/**
	 * Allow multi-item product if the product is a umbrella membership.
	 *
	 * @since 1.0
	 *
	 * @param bool $allowed
	 * @param int  $product_id
	 *
	 * @return bool
	 */
	public static function allow_multi_item_product_if_umbrella_membership( $allowed, $product_id ) {

		if ( $allowed ) {
			return true;
		}

		return it_exchange_product_has_feature( $product_id, 'umbrella-membership' );
	}

	/**
	 * When outputting the Stripe payment button,
	 * check if a umbrella membership product is being checked-out.
	 *
	 * If so, add a variety of filters.
	 *
	 * @since 1.0
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function check_for_umbrella_membership( $content ) {

		// check if we have a membership product
		if ( ! it_exchange_membership_cart_contains_membership_product() ) {
			return $content;
		}

		$product = self::get_cart_product();

		if ( ! it_exchange_product_has_feature( $product['product_id'], 'umbrella-membership' ) ) {
			return $content;
		}

		add_filter( 'it_exchange_get_cart_product_subtotal', array(
			__CLASS__,
			'override_cart_product_subtotal'
		), 10, 2 );

		return $content;
	}

	/**
	 * Once the payment button is complete,
	 * remove our filter overriding the product price.
	 *
	 * @since 1.0
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function remove_umbrella_membership_filters( $content ) {

		remove_filter( 'it_exchange_get_cart_product_subtotal', array(
			__CLASS__,
			'override_cart_product_subtotal'
		) );

		return $content;
	}

	/**
	 * Override the cart product subtotal to ignore the quantity in the cart.
	 *
	 * @since 1.0
	 *
	 * @param int|string $total
	 * @param array      $product
	 *
	 * @return int|string
	 */
	public static function override_cart_product_subtotal( $total, $product ) {
		return it_exchange_get_cart_product_base_price( $product, false );
	}

	/**
	 * Add the quantity arg to the Stripe checkout args.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function add_quantity_arg_to_stripe( $args = array() ) {

		if ( ! it_exchange_membership_cart_contains_membership_product() ) {
			return $args;
		}

		$product = self::get_cart_product();

		if ( ! it_exchange_product_has_feature( $product['product_id'], 'umbrella-membership' ) ) {
			return $args;
		}

		$args['quantity'] = it_exchange_get_cart_product_quantity( $product );

		return $args;
	}

	/**
	 * When a purchase is completed, check if it was a umbrella membership
	 * purchase.
	 *
	 * @since 1.0
	 *
	 * @param int $transaction_id
	 */
	public static function record_umbrella_membership_purchase( $transaction_id ) {

		foreach ( it_exchange_get_transaction_products( $transaction_id ) as $product ) {

			if ( ! it_exchange_product_has_feature( $product['product_id'], 'umbrella-membership' ) ) {
				continue;
			}

			if ( $product['count'] > 1 ) {
				Purchase::create( it_exchange_get_transaction( $transaction_id ) );
			}
		}
	}

	/**
	 * Display the membership paying customer information on the membership
	 * dashboard.
	 *
	 * @since 1.0
	 */
	public static function display_membership_payer_information() {

		$membership = it_exchange_membership_addon_get_current_membership();

		if ( ! $membership || ! $membership instanceof \IT_Exchange_Product ) {
			return;
		}

		$customer = it_exchange_get_current_customer();

		$purchase = itegms_get_purchase_for_members_membership( $customer, $membership );

		if ( ! $purchase ) {
			return;
		}

		$payer = $purchase->get_customer();

		$name = trim( "{$payer->wp_user->first_name} {$payer->wp_user->last_name}" );

		if ( empty( $name ) ) {
			$name = $payer->wp_user->display_name;
		}

		// todo: double check protocol
		$link = "<a href='mailto:{$payer->wp_user->user_email}'>$name</a>";

		$text = sprintf( __( 'This membership is paid for by %1$s.', 'LION' ),
			$link );

		/**
		 * Filters the paid for by text on the membership dashboard.
		 *
		 * @since 1.0
		 *
		 * @param string                $text
		 * @param Purchase              $purchase
		 * @param \IT_Exchange_Customer $customer
		 */
		$text = apply_filters( 'itegms_membership_paid_for_by_message', $text, $purchase, $customer );

		echo "<p class='itegms-paid-for-by'>$text</p>";
	}

	/**
	 * Add our templtae path.
	 *
	 * @since 1.0
	 *
	 * @param array $paths
	 *
	 * @return array
	 */
	public static function add_template_path( $paths ) {
		$paths[] = Plugin::$dir . '/src/Templates';

		return $paths;
	}

	/**
	 * Add the child member selection to the membership page in the account
	 * area.
	 *
	 * @since 1.0
	 *
	 * @param array $elements
	 *
	 * @return array
	 */
	public static function add_child_member_selection_to_membership_page( $elements ) {

		$product = it_exchange_membership_addon_get_current_membership();

		if ( ! $product || ! $product instanceof \IT_Exchange_Product ) {
			return $elements;
		}

		$query = new Purchase_Query( array(
			'customer'   => it_exchange_get_current_customer_id(),
			'membership' => $product->ID,
			'active'     => true
		) );

		if ( count( $query->get_results() ) == 0 ) {
			return $elements;
		}

		$new = array(
			'welcome-message',
			'umbrella-membership',
			'membership-content'
		);

		$old = array_diff( $elements, $new );

		if ( count( $old ) ) {
			$elements = $new + $old;
		} else {
			$elements = $new;
		}

		return $elements;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0
	 */
	public static function enqueue_scripts() {

		if ( it_exchange_is_page( 'memberships' ) ) {
			wp_enqueue_style( 'itegms-account-page' );
			wp_enqueue_script( 'itegms-account-page' );
		}
	}

	/**
	 * Save the members list.
	 *
	 * // todo refactor this
	 *
	 * @since 1.0
	 */
	public static function save_members_list() {

		if ( ! isset( $_POST['itegms-save-members'] ) || ! isset( $_POST['itegms_prod'] ) ) {
			return;
		}

		$membership = it_exchange_get_product( $_POST['itegms_prod'] );
		$payee      = it_exchange_get_current_customer();

		if ( ! $membership || ! $payee ) {
			return;
		}

		$cid = $payee->id;

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], "itegms-save-{$cid}-members" ) ) {
			it_exchange_add_message( 'error', __( "Request expired. Please try again.", 'LION' ) );

			return;
		}

		$new_users = isset( $_POST['itegms_member'] ) ? $_POST['itegms_member'] : array();

		$saved        = itegms_get_payee_members_of_product( $payee, $membership );
		$saved_emails = array_values( array_map( function ( Relationship $rel ) {
			return $rel->get_member()->wp_user->user_email;
		}, $saved ) );

		$skipped = array();

		$name_and_email_required = false;

		$to_create = array();

		foreach ( $new_users as $new_user ) {

			$email = $new_user['email'];
			$name  = $new_user['name'];

			if ( empty( $email ) xor empty( $name ) ) {
				// if either an email address or name is missing for a single member record

				if ( ! $name_and_email_required ) {
					it_exchange_add_message( 'error', __( "Member name and email are required.", 'LION' ) );

					$name_and_email_required = true;
				}

				continue;
			} elseif ( empty( $email ) && empty( $name ) ) {
				// this is just an empty row
				continue;
			}

			if ( ! in_array( $email, $saved_emails ) ) {
				$to_create[ $email ] = $name;
			} else {
				// we already have this email, mark it as such
				$skipped[] = $email;
			}
		}

		// go through all saved members if they haven't been skipped, delete them
		foreach ( $saved as $rel ) {
			if ( ! in_array( $rel->get_member()->wp_user->user_email, $skipped ) ) {
				$rel->delete();
			}
		}

		$query = new Purchase_Query( array(
			'customer'   => $cid,
			'active'     => true,
			'membership' => $membership->ID
		) );

		$purchases = array_filter( $query->get_results(), function ( Purchase $purchase ) {
			return $purchase->get_remaining_seats() > 0;
		} );

		foreach ( $to_create as $email => $name ) {

			// check to see if this user exists in the system
			$user = get_user_by( 'email', $email );

			if ( empty( $purchases ) ) {
				break;
			}

			try {
				$use = reset( $purchases );
				$i   = key( $purchases );

				if ( $user ) {
					$customer = it_exchange_get_customer( $user );

					Relationship::create( $use, $customer );
				} else {
					// this is a new email
					Relationship::create_with_email( $use, $email, $name );
				}

				if ( $use->get_remaining_seats() <= 0 ) {
					unset( $purchases[ $i ] );
				}
			}
			catch ( \Exception $e ) {
				it_exchange_add_message( 'error', $e->getMessage() );
			}
		}

		if ( ! it_exchange_has_messages( 'error' ) ) {
			it_exchange_add_message( 'notice', __( "Members Updated.", 'LION' ) );
		}
	}

	/**
	 * Override the member access data.
	 *
	 * @since 1.0
	 *
	 * @param mixed  $data
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param bool   $single
	 *
	 * @return mixed
	 */
	public static function override_member_access( $data, $object_id, $meta_key, $single ) {

		if ( ! is_null( $data ) ) {
			return $data;
		}

		if ( $meta_key !== '_it_exchange_customer_member_access' ) {
			return $data;
		}

		$query = new Relationship_Query( array(
			'member' => $object_id
		) );

		/** @var Relationship[] $umbrella_memberships */
		$umbrella_memberships = $query->get_results();

		if ( empty( $umbrella_memberships ) ) {
			return $data;
		}

		// remove our filter to prevent loops
		remove_filter( 'get_user_metadata', array(
			__CLASS__,
			'override_member_access'
		) );

		// retrieve the original data
		$original = get_user_meta( $object_id, $meta_key, $single );

		foreach ( $umbrella_memberships as $membership ) {

			if ( ! $membership->get_purchase()->is_active() ) {
				continue;
			}

			$tid = $membership->get_purchase()->get_transaction()->ID;
			$pid = $membership->get_purchase()->get_membership()->ID;

			$original[ $tid ][] = $pid;
		}

		// remove our filter to prevent loops
		add_filter( 'get_user_metadata', array(
			__CLASS__,
			'override_member_access'
		), 10, 4 );

		return array( $original );
	}

	/**
	 * When the member access user meta is updated,
	 * remove our umbrella memberships.
	 *
	 * @since 1.0
	 *
	 * @param bool   $res
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 * @param mixed  $prev_value
	 *
	 * @return bool
	 */
	public static function remove_added_member_access( $res, $object_id, $meta_key, $meta_value, $prev_value ) {

		if ( $meta_key !== '_it_exchange_customer_member_access' ) {
			return $res;
		}

		if ( empty( $meta_value ) ) {
			return $res;
		}

		$query = new Relationship_Query( array(
			'member' => $object_id
		) );

		/** @var Relationship[] $umbrella_memberships */
		$umbrella_memberships = $query->get_results();

		if ( empty( $umbrella_memberships ) ) {
			return $res;
		}

		// remove our filter to prevent loops
		remove_filter( 'update_user_metadata', array(
			__CLASS__,
			'remove_added_member_access'
		) );

		foreach ( $umbrella_memberships as $membership ) {
			unset( $meta_value[ $membership->get_purchase()->get_transaction()->ID ] );
		}

		// perform the update
		$res = update_user_meta( $object_id, $meta_key, $meta_value, $prev_value );

		// remove our filter to prevent loops
		add_filter( 'update_user_metadata', array(
			__CLASS__,
			'remove_added_member_access'
		), 10, 5 );

		return $res;
	}

	/**
	 * Handle expiring access to memberships when the subscriber status
	 * changes.
	 *
	 * This only sends out notifications. Exchange handles the actual removal
	 * of access when constructing the member access array.
	 *
	 * @since 1.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 * @param string                   $status
	 */
	public static function handle_expiry_on_subscriber_status_change( $transaction, $status ) {

		if ( $status == 'deactivated' || $status == 'cancelled' ) {

			$purchase = itegms_get_purchase_by_transaction( $transaction );

			if ( $purchase && $purchase->is_active() ) {
				$purchase->deactivate();
			}
		} elseif ( $status == 'active' ) {

			$purchase = itegms_get_purchase_by_transaction( $transaction );

			if ( $purchase && ! $purchase->is_active() ) {
				$purchase->activate();
			}
		}
	}

	/**
	 * Handle expiring access to memberships when the transaction status
	 * changes.
	 *
	 * This only sends out notifications. Exchange handles the actual removal
	 * of access when constructing the member access array.
	 *
	 * @since 1.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 * @param string                   $old_status
	 * @param bool                     $old_cleared
	 * @param string                   $status
	 */
	public static function handle_expiry_on_transaction_change( $transaction, $old_status, $old_cleared, $status ) {

		$transaction = it_exchange_get_transaction( $transaction->ID );

		if ( $old_status === $status ) {
			return;
		}

		$new_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction );

		if ( $new_cleared == $old_cleared ) {
			return;
		}

		$purchase = itegms_get_purchase_by_transaction( $transaction );

		if ( ! $purchase ) {
			return;
		}

		// transition from paid to pending
		if ( $old_cleared && ! $new_cleared && $purchase->is_active() ) {
			$purchase->deactivate();

			return;
		}

		if ( ! $old_cleared && $new_cleared && ! $purchase->is_active() ) {
			$purchase->activate();

			return;
		}
	}

	/**
	 * When a purchase is activated, transfer any expired relationships to the
	 * reactivated purchase.
	 *
	 * @since 1.0
	 *
	 * @param Purchase $activated_purchase
	 */
	public static function transfer_relationships_on_activation( Purchase $activated_purchase ) {

		$query = new Purchase_Query( array(
			'customer'       => $activated_purchase->get_customer()->id,
			'membership'     => $activated_purchase->get_membership()->ID,
			'active'         => false,
			'id__not_in'     => array( $activated_purchase->get_pk() ),
			'items_per_page' => $activated_purchase->get_remaining_seats(),
			'return_value'   => 'relationships'
		) );

		/** @var Relationship[] $relationships */
		$relationships = $query->get_results();

		foreach ( $relationships as $relationship ) {
			$relationship->set_purchase( $activated_purchase );
		}
	}

	/**
	 * When a purchase is expired, transfer its relationships to available
	 * purchases.
	 *
	 * @since 1.0
	 *
	 * @param Purchase $expired_purchase
	 */
	public static function transfer_relationships_on_expiry( Purchase $expired_purchase ) {

		$members = $expired_purchase->get_members();

		if ( empty( $members ) ) {
			return;
		}

		$query = new Purchase_Query( array(
			'customer'   => $expired_purchase->get_customer()->id,
			'membership' => $expired_purchase->get_membership()->ID,
			'active'     => true,
			'id__not_in' => array( $expired_purchase->get_pk() )
		) );

		/** @var Purchase[] $purchases */
		$purchases = array_filter( $query->get_results(), function ( Purchase $purchase ) {
			return $purchase->get_remaining_seats() > 0;
		} );

		if ( empty( $purchases ) ) {
			array_walk( $members, function ( Relationship $rel ) {
				$rel->expire();
			} );

			return;
		}

		foreach ( $members as $member ) {

			$use = reset( $purchases );
			$i   = key( $purchases );

			foreach ( $purchases as $key => $purchase ) {
				$use = $purchase;
				$i   = $key;
			}

			if ( $use != null ) {
				$member->set_purchase( $use );

				if ( $use->get_remaining_seats() <= 0 ) {
					unset( $purchases[ $i ] );
				}
			} else {
				$member->expire();
			}
		}
	}

	/**
	 * When a new purchase is created, move as many expired relationships to
	 * this new purchase object.
	 *
	 * @since 1.0
	 *
	 * @param Purchase $purchase
	 */
	public static function move_expired_relationships_on_new_purchase( Purchase $purchase ) {

		if ( ! it_exchange_transaction_is_cleared_for_delivery( $purchase->get_transaction() ) ) {
			return;
		}

		$query = new Purchase_Query( array(
			'customer'       => $purchase->get_customer()->id,
			'membership'     => $purchase->get_membership()->ID,
			'items_per_page' => $purchase->get_seats(),
			'active'         => false,
			'return_value'   => 'relationships'
		) );

		/** @var Relationship[] $relationships */
		$relationships = $query->get_results();

		foreach ( $relationships as $relationship ) {
			$relationship->set_purchase( $purchase );
		}
	}

	/**
	 * When a user is deleted, remove their purchase relationships.
	 *
	 * @since 1.0
	 *
	 * @param int $id
	 */
	public static function delete_relationships_on_user_deletion( $id ) {

		$query = new Relationship_Query( array(
			'member' => $id
		) );

		foreach ( $query->get_results() as $res ) {
			$res->delete();
		}
	}

	/**
	 * When a customer is deleted, go through all of their purchases,
	 * and delete them.
	 *
	 * @since 1.0
	 *
	 * @param int $id
	 */
	public static function delete_customer_purchases_on_user_deletion( $id ) {

		$query = new Purchase_Query( array(
			'customer' => $id
		) );

		/** @var Purchase $purchase */
		foreach ( $query->get_results() as $purchase ) {

			if ( $purchase->is_active() ) {
				$purchase->deactivate();
			}

			$purchase->delete();
		}
	}

	/**
	 * When a membership product is deleted, remove purchase and relationship records.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id
	 */
	public static function delete_purchases_on_product_deletion( $post_id ) {

		$product = it_exchange_get_product( $post_id );

		if ( ! $product || $product->product_type !== 'membership-product-type' ) {
			return;
		}

		$query = new Purchase_Query( array(
			'membership' => $post_id
		) );

		foreach ( $query->get_results() as $purchase ) {
			$purchase->delete();
		}
	}

	/**
	 * When a transaction is deleted, deactivate it and then delete the purchase record.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id
	 */
	public static function delete_purchases_on_transaction_deletion( $post_id ) {

		$transaction = it_exchange_get_transaction( $post_id );

		if ( ! $transaction ) {
			return;
		}

		$query = new Purchase_Query( array(
			'transaction' => $post_id
		) );

		/** @var Purchase $purchase */
		foreach ( $query->get_results() as $purchase ) {

			if ( $purchase->is_active() ) {
				$purchase->deactivate();
			}

			$purchase->delete();
		}
	}
}