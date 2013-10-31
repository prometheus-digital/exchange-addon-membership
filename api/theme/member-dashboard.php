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
		$this->_membership_access_rules = !empty( $this->_membership_product ) ? it_exchange_get_product_feature( $this->_membership_product->ID, 'membership-content-access-rules' ) : false;
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
		
		if ( empty( $this->_membership_product ) )
			return false;
		
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
		
		if ( empty( $this->_membership_product ) )
			return false;
		
		$product_id = $this->_membership_product->ID;
		$now = time();
		
		// Return boolean if has flag was set
		if ( $options['has'] )
			return ! empty( $this->_membership_access_rules ) ? true : false;
		
		// Repeats checks for when flags were not passed.
		if ( !empty( $this->_membership_access_rules ) ) {
			$result = '';
			
			$defaults      = array(
				'before'             => '<div class="it-exchange-restricted-content">',
				'after'              => '</div>',
				'title'              => __( 'Membership Content', 'LION' ),
				'toggle'             => true,
				'layout'             => 'grid',
				'posts_per_grouping' => 5,
			);
			
			$options = ITUtility::merge_defaults( $options, $defaults );
			
			$result .= $options['before'];	
			
			$result .= '<h2>' . $options['title'] . '</h2>';
			
			$result .= '<div class="it-exchange-content-wrapper it-exchange-content-' . $options['layout'] . ' it-exchange-clearfix">'; 
			
			$group = 1;
			$previous['group'] = 0;
			$previous['selected'] = '';
			$current['group'] = 0;
			$current['selected'] = '';
			
			foreach ( $this->_membership_access_rules as $content ) {
				if ( $content['selected'] == 'taxonomy' ||  $content['selected'] == 'post_types' || ( $content['selected'] == 'posts' && ( $previous['selected'] == 'taxonomy' || $previous['selected'] == 'post_types' ) ) ) {
					$group++;
				}
				
				$current['group'] = $group;
				
				$more_content_link = '';
				
				$term = '';
				
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
				
				if ( ! empty( $restricted_posts ) ) {

					// Closes the group.
					if ( $previous['group'] == ( $current['group'] - 1 ) && $current['group'] != 1 ) {
						$close = true;
					} else {
						$close = false;
					}
					
					// Opens a group.
					if ( $previous['group'] == ( $current['group'] - 1 ) ) {
						$open = true;
					} else {
						$open = false;
					}
					
					// Closes the last group.
					if ( $content === end( $this->_membership_access_rules ) ) {
						$close_last = true;
					} else {
						$close_last = false;
					}
					
					ob_start();
					?>
						<?php if ( 'grid' == $options['layout'] ) : ?>
							<?php if ( $close == true ) : ?>
									</div>
								</div>
							<?php endif; ?>
							
							<?php if ( $open == true ) : ?>
								<div class="it-exchange-content-group it-exchange-clearfix">
							<?php endif; ?>
							
							<?php if ( ! empty( $label ) ) : ?>
								<div class="it-exchange-content-group-header it-exchange-clearfix">
									<span class="it-exchange-content-group-title it-exchange-left"><?php echo $label; ?></span>
									<?php if ( ! empty( $term ) ) : ?>
										<?php if ( $term->count > count( $restricted_posts ) ) : ?>
											<span class="it-exchange-content-group-count it-exchange-right"><?php echo sprintf( __( '%d of %d Items - %sView All%s', 'LION' ), count( $restricted_posts ), $term->count, '<a href="' . $more_content_link . '">', '</a>' ) ?></span>
										<?php else : ?>
											<span class="it-exchange-content-group-count it-exchange-right"><?php echo count( $restricted_posts ); ?> <?php echo ( count( $restricted_posts ) == 1 ) ? __( 'Item', 'LION' ) : __( 'Items', 'LION' ); ?></span>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							
							<?php if ( $open == true ) : ?>
								<div class="it-exchange-content-items it-exchange-columns-wrapper">
							<?php endif; ?>
							
							<?php foreach ( $restricted_posts as $post ) : ?>
								<?php
									$dripping = array();
									
									if ( $content['selected'] == 'posts' ) {
										$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
										if ( $interval > 0 ) {
											$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
											$duration = ! empty( $duration ) ? $duration : 'days';
											$member_access = it_exchange_get_session_data( 'member_access' );
											$key = array_search( $product_id, $member_access );
											if ( $key !== false ) {
												$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $key ) );
												$dripping['time'] = strtotime( $interval . ' ' . $duration, $purchase_time );
											}
										}
									}
									
									if ( ! empty( $dripping ) && $dripping['time'] > $now ) {
										$dripping['drip'] = true;
										$dripping['earliest'] = $dripping['time'] - $now;
									} else {
										$dripping['drip'] = false;
									}
								?>
								<div class="it-exchange-content-item it-exchange-content-<?php echo $post->post_type; ?> it-exchange-content-<?php echo ( $dripping['drip'] == true ) ? 'unavailable' : 'available'; ?> it-exchange-column">
									<div class="it-exchange-column-inner">
										<div class="it-exchange-columns-wrapper">
											<div class="it-exchange-content-item-icon it-exchange-column">
												<?php if ( $dripping['drip'] == true ) : ?>
													<span class="it-exchange-item-icon"></span>
												<?php else : ?>
													<a class="it-exchange-item-icon" href="<?php echo get_permalink( $post->ID ); ?>"></a>
												<?php endif; ?>
											</div>
											<div class="it-exchange-content-item-info it-exchange-column">
												<div class="it-exchange-column-inner">
													<?php if ( $dripping['drip'] == true ) : ?>
														<span class="it-exchange-item-unavailable-message"><?php echo sprintf( __( 'available in %s days', 'LION' ), ceil( $dripping['earliest'] / 60 / 60 / 24 ) ); ?></span>
														<span class="it-exchange-item-title"><?php echo get_the_title( $post->ID ); ?></span>
													<?php else : ?>
														<a class="it-exchange-item-title" href="<?php echo get_permalink( $post->ID ); ?>"><span><?php echo get_the_title( $post->ID ); ?></span></a>
													<?php endif; ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php elseif ( 'list' == $options['layout'] ) : ?>
							<?php if ( $content['selected'] == 'posts' ) : ?>
								<?php foreach ( $restricted_posts as $post ) : ?>
									<?php
										$dripping = array();
									
										if ( $content['selected'] == 'posts' ) {
											$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
											if ( $interval > 0 ) {
												$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
												$duration = ! empty( $duration ) ? $duration : 'days';
												$member_access = it_exchange_get_session_data( 'member_access' );
												$key = array_search( $product_id, $member_access );
												if ( $key !== false ) {
													$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $key ) );
													$dripping['time'] = strtotime( $interval . ' ' . $duration, $purchase_time );
												}
											}
										}
									
										if ( ! empty( $dripping ) && $dripping['time'] > $now ) {
											$dripping['drip'] = true;
											$dripping['earliest'] = $dripping['time'] - $now;
										} else {
											$dripping['drip'] = false;
										}
									?>
									<div class="it-exchange-content-group it-exchange-content-<?php echo $post->post_type; ?> it-exchange-content-<?php echo ( $dripping['drip'] == true ) ? 'unavailable' : 'available'; ?>">
										<?php if ( $dripping['drip'] == true ) : ?>
											<p class="it-exchange-group-content-label">
												<span class="it-exchange-item-unavailable-message it-exchange-right"><?php echo sprintf( __( 'available in %s days', 'LION' ), ceil( $dripping['earliest'] / 60 / 60 / 24 ) ); ?></span>
												<span class="it-exchange-item-title"><?php echo get_the_title( $post->ID ); ?></span>
											</p>
										<?php else : ?>
											<p class="it-exchange-group-content-label"><a class="it-exchange-item-title" href="<?php echo get_permalink( $post->ID ); ?>"><span><?php echo get_the_title( $post->ID ); ?></span></a></p>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="it-exchange-content-group it-exchange-content-<?php echo ( $options['toggle'] == true ) ? 'toggle' : 'open'; ?>">
									<p class="it-exchange-group-content-label"><?php echo $label; ?><?php echo ( $options['toggle'] == true ) ? '<span class="it-exchange-open-group"></span>' : ''; ?></p>
									<ul class="<?php echo ( $options['toggle'] == true ) ? 'it-exchange-hidden' : '' ?>">
										<?php foreach ( $restricted_posts as $post ) : ?>
											<?php echo '<li><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></li>' ?>
										<?php endforeach; ?>
										<?php if ( ! empty( $more_content_link ) && $options['posts_per_grouping'] <= count( $restricted_posts ) ) : ?>
											<?php echo '<li class="it-exchange-content-more"><a href="' . $more_content_link . '">' . __( 'Read More Content In This Group', 'LION' ) . '</a></li>'; ?>
										<?php endif; ?>
									</ul>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					<?php
					$result .= ob_get_clean();
				}
				// Setting the group and selected.
				$previous['group'] = $current['group'];
				$previous['selected'] = $content['selected'];
			}
			
			$result .= '</div>';
			
			$result .= $options['after'];
			
			return $result;
		}
		return false;
	}
}
