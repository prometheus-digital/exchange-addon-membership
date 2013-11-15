<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
*/

/**
 * Creates a shortcode that returns content template parts for pages
 *
 * @since 0.4.8
 *
 * @param array $atts attributes passed in via shortcode arguments
 * @return string the template part
*/
function it_exchange_membership_addon_add_included_content_shortcode( $atts ) {
	global $post;
	
	$membership_settings = it_exchange_get_option( 'addon_membership' );
	
	$defaults = array(
		'product_id' => !empty( $post->ID ) ? $post->ID : false,
		'before'             => '<div class="it-exchange-restricted-content">',
		'after'              => '</div>',
		'title'              => '',
		'toggle'             => 'on',
		'posts_per_grouping' => 5,
		'show_drip'          => 'on',
		'show_drip_time'     => 'on',
		'show_icon'          => 'on',
		'layout'             => $membership_settings['memberships-dashboard-view']
	);
	$atts = shortcode_atts( $defaults, $atts );
		
	$product_type = it_exchange_get_product_type( $atts['product_id'] );
			
	if ( 'membership-product-type' === $product_type ) {
		
		$rules = it_exchange_get_product_feature( $atts['product_id'], 'membership-content-access-rules' );
						
		// Repeats checks for when flags were not passed.
		if ( !empty( $rules ) ) {

			$result = '';
			$result .= '<div class="it-exchange-membership-membership-content">';
			
			if ( !empty( $atts['title'] ) )
				$result .= '<h4>' . $atts['title'] . '</h4>';
				
			$result .= '<div class="it-exchange-content-wrapper it-exchange-content-' . $atts['layout'] . ' it-exchange-clearfix">'; 
			
            $groupings = array();
			
			foreach ( $rules as $rule ) {
			
				$restricted_posts = array();
				$selection    = !empty( $rule['selection'] )    ? $rule['selection'] : false;
				$selected     = !empty( $rule['selected'] )     ? $rule['selected'] : false;
				$value        = !empty( $rule['term'] )         ? $rule['term'] : false;
				$group        = isset( $rule['group'] )         ? $rule['group'] : NULL;
				$group_id     = isset( $rule['group_id'] )      ? $rule['group_id'] : false;
				$grouped_id   = isset( $rule['grouped_id'] )    ? $rule['grouped_id'] : false;
												
				if ( !empty( $groupings ) && $grouped_id !== end( $groupings ) ) {
					$result .= '</ul>'; //this is ending the uls from the group opening
					$result .= '</div>'; //this is ending the divs from the group opening
					array_pop( $groupings );
				
				} else if ( false === $grouped_id && !empty( $groupings ) ) {
								
					foreach( $groupings as $group ) {
						$result .= '</ul>'; //this is ending the uls from the group opening
						$result .= '</div>'; //this is ending the divs from the group opening
					}
					$groupings = array();
				
				}
					
				if ( false !== $group_id ) {
				
					$group_layout = !empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
					$result .= '<div class="it-exchange-content-group it-exchange-content-group-layout-' . $group_layout . '">';
					$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-item-title">' . $group . '</span><span class="it-exchange-open-group"></span></p>';
					$result .= '<ul class="it-exchange-hidden">';
				
				} else if ( !empty( $selected ) ) {
					
					switch ( $selected ) {
						
						case 'taxonomy':
							$term = get_term_by( 'id', $value, $selection );
							$label = $term->name;
							$args = array(
								'posts_per_page' => $atts['posts_per_grouping'],
								'tax_query' => array(
									array(
										'taxonomy' => $selection,
										'field' => 'id',
										'terms' => $value
									)
								)
							);
							$restricted_posts = get_posts( $args );
							break;
						
						case 'post_types':
							$post_type = get_post_type_object( $value );
							$label = $post_type->labels->name;
							$args = array(
								'post_type'      => $value,
								'posts_per_page' => $atts['posts_per_grouping'],
							);
							$restricted_posts = get_posts( $args );
							break;
							
						case 'posts':
							$label = '';
							$args = array(
								'p'         => $value,
								'post_type' => 'any',
							);
							$restricted_posts = get_posts( $args );
							break;
						
					}
					
					if ( !empty( $restricted_posts ) ) {
						$result .= $atts['before'];	
						
						if ( !empty( $label ) ) {
							// We're in a group.
							
							if ( 'on' == $atts['toggle'] ) {
								$group_layout = !empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
								$result .= '<div class="it-exchange-content-group it-exchange-content-group-layout-' . $group_layout . '">';
								$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-item-title">' . $label . '</span><span class="it-exchange-open-group"></span></p>';
								$result .= '<ul class="it-exchange-hidden">';
								
								foreach( $restricted_posts as $restricted_post ) {
									
									$result .= '<li>';
									$result .= '	<div class="it-exchange-content-group it-exchange-content-single">';
									$result .= '		<div class="it-exchange-content-item-icon"><span class="it-exchange-item-icon"></span></div>';
									$result .= '		<div class="it-exchange-content-item-info"><p class="it-exchange-group-content-label">' . get_the_title( $restricted_post->ID ) . '</p></div>';
									$result .= '	</div>';
									$result .= '</li>';
								}
								
								if ( $atts['posts_per_grouping'] <= count( $restricted_posts ) )
									$result .= '<li class="it-exchange-content-more">' . __( 'And More Content In This Group', 'LION' ) . '</li>';
								
								$result .= '</ul>';
								$result .= '</div>';
							} else {
								$result .= '<p class="it-exchange-group-content-label"><span class="it-exchange-item-title">' . $label . '</span></p>';
								$result .= '<ul>';
								foreach( $restricted_posts as $restricted_post ) {
									$result .= '<li>' . get_the_title( $restricted_post->ID ) . '</li>';
								}
								
								if ( $atts['posts_per_grouping'] <= count( $restricted_posts ) )
									$result .= '<li class="it-exchange-content-more">' . __( 'And More Content In This Group', 'LION' ) . '</li>';
								
								$result .= '</ul>';
							}
						} else {
							foreach( $restricted_posts as $restricted_post ) { //should just be a regular post
								
								$drip_label = '';
							
								if ( 0 < $interval = get_post_meta( $restricted_post->ID, '_item-content-rule-drip-interval-' . $atts['product_id'], true ) ) {
									
									if ( 'on' !== $atts['show_drip'] )
										continue;
									
									if ( 'on' === $atts['show_drip_time'] ) {
										$duration = get_post_meta( $restricted_post->ID, '_item-content-rule-drip-duration-' . $atts['product_id'], true );
										$duration = !empty( $duration ) ? $duration : 'days';
										
										$now = strtotime( 'midnight', time() );
										$dripping = strtotime( $interval . ' ' . $duration, $now );
										$earliest_drip = $dripping - $now;
										$drip_label = ' <span class="it-exchange-restricted-content-drip-label">(' . sprintf( __( 'available in %s days', 'LION' ), ceil( $earliest_drip / 60 / 60 / 24 ) ) . ')</span>';
									}
									
								}
								
								$result .= '<li>';	
								$result .= '<div class="it-exchange-content-group it-exchange-content-single it-exchange-content-available">';
								$result .= '	<div class="it-exchange-content-item-icon"><span class="it-exchange-item-icon"></span></div>';
								$result .= '	<div class="it-exchange-content-item-info"><p class="it-exchange-group-content-label">' . get_the_title( $restricted_post->ID ) . '</p></div>';
								$result .= '</div>';
								$result .= '</li>';
							}
						}
						
						$result .= $atts['after'];
					}
				
				}
								
				if ( false !== $group_id && !in_array( $group_id, $groupings ) )
					$groupings[] = $group_id;
			
			}
			
			$result .= '</div></div>';
			
			if ( !empty( $groupings ) ) {
				foreach( $groupings as $group ) {
					$result .= '</div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
				}
				$groupings = array();
			}
			
			return $result;
		}
		
	}
	return false;
}
add_shortcode( 'it-exchange-membership-included-content', 'it_exchange_membership_addon_add_included_content_shortcode' );