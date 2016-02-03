<?php
/**
 * Abstract rule class.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_AbstractContent_Rule
 */
abstract class IT_Exchange_Membership_AbstractContent_Rule implements IT_Exchange_Membership_Content_RuleInterface {

	/**
	 * @var IT_Exchange_Membership_Delay_RuleInterface[]
	 */
	private $delay_rules = array();

	/**
	 * Add a delay rule to this content rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface $delay_rule
	 *
	 * @return self
	 */
	public function add_delay_rule( IT_Exchange_Membership_Delay_RuleInterface $delay_rule ) {
		$this->delay_rules[] = $delay_rule;

		return $this;
	}

	/**
	 * Get all the delay rules.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_RuleInterface[]
	 */
	public function get_delay_rules() {
		return $this->delay_rules;
	}
}