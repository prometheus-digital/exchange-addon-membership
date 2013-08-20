<?php
/**
 * Restricted Content class for THEME API in Membership Add-on
 *
 * @since 1.0.0
*/

class IT_Theme_API_Restricted implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'restricted';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	public $_tag_map = array(
		'content' => 'content',
		'excerpt' => 'excerpt',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Restricted() {
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
	function content( $options=array() ) {
		$defaults = array(
			'more_link_text' => null,
			'strip_teaser' => false
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
			
		remove_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );
		remove_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );
		
		$content = get_the_content( $options['more_link_text'], $options['strip_teaser'] );
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = '<h1>You must be logged in with the correct membership level to access this content</h1>';
		
		add_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );
		add_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );
		
		return $content;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function excerpt( $options=array() ) {
		remove_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );
		remove_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );
		
		$excerpt = apply_filters( 'the_excerpt', get_the_excerpt() );
		$excerpt = '<h1>You must be logged in with the correct membership level to access this excerpt</h1>';
		
		add_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );
		add_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );
		
		return $excerpt;
	}
}
