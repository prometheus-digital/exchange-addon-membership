<?php
/**
 * Membership Object Type.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Membership_Object_Type
 */
class ITE_Membership_Object_Type implements ITE_Object_Type {

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'membership';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return __( 'Membership', 'LION' );
	}

	/**
	 * @inheritDoc
	 */
	public function create_object( array $attributes ) {
		throw new BadMethodCallException( 'create_object not supported.' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_by_id( $id ) {

		if ( class_exists( '\IT_Exchange_Subscription' ) ) {
			try {
				$subscription = IT_Exchange_Subscription::get( $id );

				if ( $subscription ) {
					return new IT_Exchange_User_Membership_Subscription_Driver( $subscription );
				}

			} catch ( InvalidArgumentException $e ) {

			}
		}

		$parts = explode( ':', $id );

		if ( count( $parts ) !== 2 ) {
			return null;
		}

		$transaction = it_exchange_get_transaction( $parts[0] );
		$product     = it_exchange_get_product( $parts[1] );

		if ( ! $transaction || ! $product instanceof IT_Exchange_Membership ) {
			return null;
		}

		return new IT_Exchange_User_Membership_Transaction_Driver( $transaction, $product );
	}

	/**
	 * @inheritDoc
	 */
	public function get_objects( \Doctrine\Common\Collections\Criteria $criteria = null ) {
		throw new BadMethodCallException( 'get_objects() not supported.' );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_object_by_id( $id ) {
		throw new BadMethodCallException( 'delete_object_by_id not supported.' );
	}

	/**
	 * @inheritDoc
	 */
	public function supports_meta() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function is_restful() {
		return false; // For now
	}
}