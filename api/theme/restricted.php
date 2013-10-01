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
			'before' => '',
			'after'  => '',
			'message' => __( 'This content is for members only. Please sign up to get access to this content and other awesome content added for members only.', 'LION' ),
			'class'  => 'it-exchange-membership-restricted-content',
		//	'more_link_text' => null,
		//	'strip_teaser' => false
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
			
		$content  = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $content;
	}

	/**
	 * @since 1.0.0
	 * @return string
	*/
	function excerpt( $options=array() ) {
		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => __( 'This content is for members only. Please sign up to get access to this content and other awesome content added for members only.', 'LION' ),
			'class'   => 'it-exchange-membership-restricted-excerpt',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$content  = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];
		
		return $excerpt;
	}
}
