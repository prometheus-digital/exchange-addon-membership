<?php
/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0
*/

class IT_Theme_API_Member_Dashboard implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'member-dashboard';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 * @since 1.0.0
	*/
	private $_customer = '';
	

	/**
	 * Current membership product being viewed
	 * @var string $_membership_product
	 * @since 1.0.0
	*/
	private $_membership_product = '';
	

	/**
	 * Current membership access rules for membership product being viewed
	 * @var string $_membership_access_rules
	 * @since 1.0.0
	*/
	private $_membership_access_rules = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'welcomemessage'    => 'welcome_message',
		'membershipcontent' => 'membership_content',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Member_Dashboard() {
		if ( is_user_logged_in() )
			$this->_customer = it_exchange_get_current_customer();
			
		$this->_membership_product = it_exchange_membership_addon_get_current_membership();
		$this->_membership_access_rules = it_exchange_get_product_feature( $this->_membership_product->ID, 'membership-content-access-rules' );
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
	function welcome_message( $options=array() ) {
		
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-welcome-message' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-welcome-message' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-welcome-message' )	
				&& it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-welcome-message' ) ) {
			$result        = false;
			$message       = it_exchange_get_product_feature( $this->_membership_product->ID, 'membership-welcome-message' );
			$defaults      = array(
				'before' => '<div class="entry-content">',
				'after'  => '</div>',
				'title'              => __( 'Welcome', 'LION' ),
			);
			$options      = ITUtility::merge_defaults( $options, $defaults );
			
			$result .= '<h2>' . $options['title'] . '</h2>';
			$result .= $options['before'];
			$result .= $message;
			$result .= $options['after'];
				
			return $result;
		}
		return false;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function membership_content( $options=array() ) {
		
		$product_id = $this->_membership_product->ID;
		$now = time();
		
		// Return boolean if has flag was set
		if ( $options['has'] )
			return !empty( $this->_membership_access_rules ) ? true : false;
		
		// Repeats checks for when flags were not passed.
		if ( !empty( $this->_membership_access_rules ) ) {
			$result = '';
			$defaults      = array(
				'before'             => '<div class="it-exchange-restricted-content">',
				'after'              => '</div>',
				'title'              => __( 'Membership Content', 'LION' ),
				'toggle'             => true,
				'posts_per_grouping' => 5,
			);
			$options      = ITUtility::merge_defaults( $options, $defaults );
			
			$result .= '<h2>' . $options['title'] . '</h2>';
	
			foreach ( $this->_membership_access_rules as $content ) {
				
				$more_content_link = '';
				
				switch ( $content['selected'] ) {
					
					case 'taxonomy':
						$term = get_term_by( 'id', $content['term'], $content['selection'] );
						$label = $term->name;
						$args = array(
							'posts_per_page' => $options['posts_per_grouping'],
							'tax_query' => array(
								array(
									'taxonomy' => $content['selection'],
									'field' => 'id',
									'terms' => $content['term']
								)
							)
						);
						$restricted_posts = get_posts( $args );
						$more_content_link = get_term_link( $term, $content['selection'] );
						break;
					
					case 'post_types':
						$post_type = get_post_type_object( $content['term'] );
						$label = $post_type->labels->name;
						$args = array(
							'post_type'      => $content['term'],
							'posts_per_page' => $options['posts_per_grouping'],
						);
						$restricted_posts = get_posts( $args );
						switch( $content['term'] ) {
							
							case 'post':
								$more_content_link = get_home_url();
								break;
								
							default:
								$more_content_link = get_post_type_archive_link( $content['term'] );
								break;
						}
						break;
						
					case 'posts':
						$label = '';
						$args = array(
							'p'         => $content['term'],
							'post_type' => 'any',
						);
						$restricted_posts = get_posts( $args );
						$more_content_link = '';
						break;
					
				}
				
				if ( !empty( $restricted_posts ) ) {
			
					$result .= $options['before'];	
					
					if ( !empty( $label ) ) {
						// We're in a group.
						if ( true == $options['toggle'] ) {
							$result .= '<div class="it-exchange-content-group">';
							
							$result .= '<p class="it-exchange-group-content-label">' . $label . '<span class="it-exchange-open-group"></span></p>';
							
							$result .= '<ul class="it-exchange-hidden">';
							
							foreach( $restricted_posts as $post ) {
								$result .= '<li><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></li>';
							}
							
							if ( ! empty( $more_content_link ) && $options['posts_per_grouping'] <= count( $restricted_posts ) )
								$result .= '<li class="it-exchange-content-more"><a href="' . $more_content_link . '">' . __( 'Read More Content In This Group', 'LION' ) . '</a></li>';
							
							$result .= '</ul>';
							
							$result .= '</div>';
						} else {
							$result .= '<p class="it-exchange-group-content-label">' . $label . '</h3>';
							
							$result .= '<ul>';
							foreach( $restricted_posts as $post ) {
								$result .= '<li><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></li>';
							}
							
							if ( !empty( $more_content_link ) && $options['posts_per_grouping'] <= count( $restricted_posts ) )
								$result .= '<li class="it-exchange-content-more"><a href="' . $more_content_link . '">' . __( 'Read More Content In This Group', 'LION' ) . '</a></li>';
							
							$result .= '</ul>';
						}
					} else {
						foreach( $restricted_posts as $post ) { //should just be a regular post
							if ( 0 < $interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true ) ) {
								$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
								$duration = !empty( $duration ) ? $duration : 'days';
								$member_access = it_exchange_get_session_data( 'member_access' );
								if ( false !== $key = array_search( $product_id, $member_access ) ) {
									$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $key ) );
									$dripping = strtotime( $interval . ' ' . $duration, $purchase_time );
									if ( $dripping < $now )						
										$result .= '<p class="it-exchange-content-item it-exchange-membership-drip-available"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></p>';
									else {																
										$earliest_drip = $dripping - $now;
										$result .= '<p class="it-exchange-content-item it-exchange-membership-drip-unavailable">' . get_the_title( $post->ID ) . ' (' . sprintf( __( 'available in %s days', 'LION' ), ceil( $earliest_drip / 60 / 60 / 24 ) ) . ')</p>';
									}
								}
								
							} else {
								$result .= '<p class="it-exchange-content-item"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></p>';
							}
						}
					}
					
					$result .= $options['after'];
				}
			}
			
			return $result;
		}
		return false;
	}
}
