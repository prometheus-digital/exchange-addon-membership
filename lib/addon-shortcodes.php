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
	
	$defaults = array(
		'product_id' => !empty( $post->ID ) ? $post->ID : false,
		'before'             => '<div class="it-exchange-restricted-content">',
		'after'              => '</div>',
		'title'              => '',
		'toggle'             => 'on',
		'posts_per_grouping' => 5,
	);
	$atts = shortcode_atts( $defaults, $atts );
		
	$product_type = it_exchange_get_product_type( $atts['product_id'] );
			
	if ( 'membership-product-type' === $product_type ) {
		
		$rules = it_exchange_get_product_feature( $atts['product_id'], 'membership-content-access-rules' );
		
		// Repeats checks for when flags were not passed.
		if ( !empty( $rules ) ) {
			$result = '';
			
			if ( !empty( $atts['title'] ) )
				$result .= '<h4>' . $atts['title'] . '</h4>';
	
			foreach ( $rules as $rule ) {
				
				switch ( $rule['selected'] ) {
					
					case 'taxonomy':
						$term = get_term_by( 'id', $rule['term'], $rule['selection'] );
						$label = $term->name;
						$args = array(
							'posts_per_page' => $atts['posts_per_grouping'],
							'tax_query' => array(
								array(
									'taxonomy' => $rule['selection'],
									'field' => 'id',
									'terms' => $rule['term']
								)
							)
						);
						$restricted_posts = get_posts( $args );
						break;
					
					case 'post_types':
						$post_type = get_post_type_object( $rule['term'] );
						$label = $post_type->labels->name;
						$args = array(
							'post_type'      => $rule['term'],
							'posts_per_page' => $atts['posts_per_grouping'],
						);
						$restricted_posts = get_posts( $args );
						break;
						
					case 'posts':
						$label = '';
						$args = array(
							'p'         => $rule['term'],
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
							$result .= '<div class="it-exchange-content-group">';
							$result .= '<p class="it-exchange-group-content-label">' . $label . '<span class="it-exchange-open-group"></span></p>';
							$result .= '<ul>';
							
							foreach( $restricted_posts as $restricted_post ) {
								$result .= '<li>' . get_the_title( $restricted_post->ID ) . '</li>';
							}
							
							if ( $atts['posts_per_grouping'] <= count( $restricted_posts ) )
								$result .= '<li class="it-exchange-content-more">' . __( 'And More Content In This Group', 'LION' ) . '</li>';
							
							$result .= '</ul>';
							$result .= '</div>';
						} else {
							$result .= '<p class="it-exchange-group-content-label">' . $label . '</h3>';
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
							$result .= '<p class="it-exchange-content-item">' . get_the_title( $restricted_post->ID ) . '</p>';
						}
					}
					
					$result .= $atts['after'];
				}
			}
			
			return $result;
		}
		
	}
	return false;
}
add_shortcode( 'it-exchange-membership-included-content', 'it_exchange_membership_addon_add_included_content_shortcode' );