<?php
/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0 
*/

class IT_Theme_API_Membership_Product implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0 
	*/
	private $_context = 'membership-product';

	/**
	 * Current product in iThemes Exchange Global
	 * @var object $product
	 * @since 0.4.0
	*/
	private $product;
	
	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0 
	*/
	public $_tag_map = array(
		'intendedaudience' => 'intended_audience',
		'objectives'       => 'objectives',
		'prerequisites'    => 'prerequisites',
		'upgradedetails'   => 'upgrade_details',
		'downgradedetails' => 'downgrade_details',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Membership_Product() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function intended_audience( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-intended-audience-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function objectives( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-objectives-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function prerequisites( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-prerequisites-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}
	
	/**
	 * @since CHANGEME
	 * @return string
	*/
	function upgrade_details( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'before_desc'  => '<p class="description">',
			'after_desc'   => '</p>',
			'upgrade_desc' => __( 'Upgrade Details:' ,'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'children' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'children' ) ) ) {
				
			$child_ids = setup_recursive_member_access_array( array( $this->product->ID ) );
			
			if ( !empty( $child_ids ) ) {
				$base_price = it_exchange_get_product_feature( $this->product->ID, 'base-price' );
				$db_product_price = it_exchange_convert_to_database_number( $base_price );
				$most_priciest = 0;
				$most_priciest_txn_id = 0;
				
				$parent_memberships = it_exchange_get_session_data( 'parent_access' );
				
				foreach ( $parent_memberships as $txn_id => $parent_id ) {
					if ( $parent_id != $this->product->ID && in_array( $parent_id, $child_ids ) ) {
						$product = it_exchange_get_product( $parent_id );
						$parent_product_base_price = it_exchange_get_product_feature( $parent_id, 'base-price' );
						$db_price = it_exchange_convert_to_database_number( $parent_product_base_price );
						if ( $db_price > $most_priciest ) {
							$most_priciest = $db_price;
							$most_priciest_txn_id = $txn_id;
							$most_producty = $product;
						}
					}
				}
				
				if ( !empty( $most_priciest_txn_id ) ) {
					
					$days_this_year = date_i18n( 'z', mktime( 0,0,0,12,31,date_i18n('Y') ) );
					$is_auto_renew = false;
									
					if ( it_exchange_product_has_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
						if ( $auto_renew = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
							$is_auto_renew = 'on' === $auto_renew ? true : false;
						}
					}
					
					if ( $is_auto_renew ) {
						//We have to have a time and it can't be forever because we're auto-renewing!
						$existimg_membership_time = it_exchange_get_product_feature( $most_producty->ID, 'recurring-payments', array( 'setting' => 'time' ) ); 
						$upgrade_membership_time = it_exchange_get_product_feature( $this->product->ID, 'recurring-payments', array( 'setting' => 'time' ) ); 
						
						//Try to get the latest child, if one exists
						$args = array( 
						    'post_parent' => $most_priciest_txn_id,
						    'post_type'   => 'it_exchange_tran',
						    'numberposts' => 1,
						    'post_status' => 'any'
						);
						$children = get_posts( $args );
						
						if ( !empty( $children ) ) {
							$transaction = it_exchange_get_transaction( $children[0]->ID );
						} else {
							$transaction = it_exchange_get_transaction( $most_priciest_txn_id );
						}
						
						$last_payment = $transaction->cart_details->total;
						
						if ( 0 === $last_payment ) {
							//get upgrade details if they exist
							//and possibly quit
						} else {
							switch( $existimg_membership_time ) {
								case 'monthly':
									$daily_cost_of_existing_membership = ( $last_payment * 12 ) / $days_this_year;
									break;
									
								case 'yearly':
									$daily_cost_of_existing_membership = $last_payment / $days_this_year;
									break;
							}		
						}
						
						$last_payment_date = date_i18n( 'z', strtotime( $transaction->post_date ) );
						$todays_date = date_i18n( 'z', time() );
						if ( $todays_date < $last_payment_date )
							$todays_date += $days_this_year;
						$remaining_days = $todays_date - $last_payment_date;
						$credit = $remaining_days * $daily_cost_of_existing_membership;
						
						switch( $upgrade_membership_time ) {
							case 'monthly':
								$daily_cost_of_upgrade_membership = ( $base_price * 12 ) / $days_this_year;
								break;
								
							case 'yearly':
								$daily_cost_of_upgrade_membership = $base_price / $days_this_year;
								break;
						}
						$free_days = floor( $credit / $daily_cost_of_upgrade_membership );
						
						if ( $free_days ) {
							
							$next_payment_due = '+' . $free_days .' Days';
							
							$transaction_method = it_exchange_get_transaction_method( $transaction->ID );
							
							//For cancelling, I need to get the subscription ID and payment method
							//And since I've done all this hard work, I should store the other pertinent information
							$upgrade_details = it_exchange_get_session_data( 'upgrade_details' );
							$upgrade_details[$this->product->ID] = array(
								'credit'             => $credit,
								'free_days'          => $free_days,
								'subscriber_id'      => $transaction->get_transaction_meta( 'subscriber_id' ),
								'transaction_method' => $transaction->transaction_method,
							);
							it_exchange_update_session_data( 'upgrade_details', $upgrade_details );
												
							$result = $options['before_desc'] . $options['upgrade_desc'] . sprintf( __( ' %s days free, then ', 'LION' ), $free_days ) . $options['after_desc'];
							
						} else {
							return;
						}
					
					} else {
						//Current membership is not auto-renewing
						
					}
					
				}
				
			}
			
		}
		
		return $result;
	}
	
	/**
	 * @since CHANGEME
	 * @return string
	*/
	function downgrade_details( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'parents' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'parents' ) ) ) {
			
			//$result = $options['before_desc'] . __( '(downgrade)', 'LION' ) . $options['after_desc'];
			
		}
		
		return $result;
	}
}
