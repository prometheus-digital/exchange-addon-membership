<?php
/**
 * Rule group.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Content_Rule_Group
 */
class IT_Exchange_Membership_Content_Rule_Group {

	const L_GRID = 'grid';
	const L_LIST = 'list';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $layout = self::L_GRID;

	/**
	 * @var int
	 */
	private $ID;

	/**
	 * @var IT_Exchange_Membership_Content_RuleInterface[]
	 */
	private $rules = array();

	/**
	 * IT_Exchange_Membership_Rule_Group constructor.
	 *
	 * @param     $name
	 * @param     $layout
	 * @param int $ID
	 */
	public function __construct( $name, $layout, $ID ) {
		$this->name   = $name;
		$this->layout = $layout;
		$this->ID     = $ID;
	}

	/**
	 * Add a rule to this group.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface $rule
	 *
	 * @return $this
	 */
	public function add_rule( IT_Exchange_Membership_Content_RuleInterface $rule ) {
		$this->rules[] = $rule;

		return $this;
	}

	/**
	 * Get the rules in this group.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Content_RuleInterface[]
	 */
	public function get_rules() {
		return $this->rules;
	}

	/**
	 * Get the name of this group.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the layout of this group.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_layout() {
		return $this->layout;
	}

	/**
	 * Get the ID of this group.
	 *
	 * @since 1.18
	 *
	 * @return int
	 */
	public function get_ID() {
		return $this->ID;
	}


}