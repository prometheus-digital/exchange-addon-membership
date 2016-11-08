<?php
/**
 * Membership Serializer.
 *
 * @since   1.20.0
 * @license GPLv2
 */

namespace iThemes\Exchange\Membership\REST\Memberships;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Memberships
 */
class Serializer {

	/**
	 * Serialize a user membership.
	 *
	 * @since 1.20.0
	 *
	 * @param \IT_Exchange_User_Membership $user_membership
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_User_Membership $user_membership ) {

		$u = $user_membership;

		$data = array(
			'id'            => $u->get_id(),
			'beneficiary'   => $u->get_user()->ID,
			'start_date'    => mysql_to_rfc3339( $u->get_start_date()->format( 'Y-m-d H:i:s' ) ),
			'end_date'      => $u->get_end_date() ? mysql_to_rfc3339( $u->get_end_date()->format( 'Y-m-d H:i:s' ) ) : '',
			'membership'    => $u->get_membership() ? $u->get_membership()->ID : 0,
			'status'        => array( 'slug' => $u->get_status(), 'label' => $u->get_status( true ) ),
			'auto_renewing' => $u->is_auto_renewing(),
			'grants_access' => $u->current_status_grants_access(),
		);

		return $data;
	}

	/**
	 * Get the user membership schema.
	 *
	 * @since 1.20.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'user-membership',
			'type'       => 'object',
			'properties' => array(
				'id'            => array(
					'description' => __( 'The unique ID for this membership.', 'LION' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'beneficiary'   => array(
					'description' => __( 'The customer receiving the benefits of this membership.', 'LION' ),
					'type'        => 'integer',
					'readonly'    => true,
				),
				'start_date'    => array(
					'description' => __( 'Membership Start Date', 'LION' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'end_date'      => array(
					'description' => __( 'Membership End Date', 'LION' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'membership'    => array(
					'description' => __( 'The membership product.', 'LION' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'auto_renewing' => array(
					'description' => __( 'Does this membership auto-renew.', 'LION' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'        => array(
					'description' => __( 'The membership status.', 'LION' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array(
							'type'       => 'object',
							'properties' => array(
								'slug'  => array(
									'type'        => 'string',
									'description' => __( 'The status slug.', 'LION' ),
									'context'     => array( 'edit' )
								),
								'label' => array(
									'type'        => 'string',
									'description' => __( 'The status label.', 'LION' ),
									'context'     => array( 'view', 'edit' )
								),
							),
						),
						array( 'type' => 'string' )
					),
				),
				'grants_access' => array(
					'type'        => 'boolean',
					'description' => __( "Does the membership's current status grant access to content.", 'LION' ),
					'context'     => array( 'view', 'edit' ),
					'readony'     => true,
				),
			),
		);
	}
}