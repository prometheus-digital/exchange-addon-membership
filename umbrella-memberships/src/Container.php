<?php
/**
 * Container for primitive types.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace ITEGMS;

/**
 * Class Container
 *
 * @package ITEGMS
 */
final class Container implements \Serializable {

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * Constructor.
	 *
	 * @param mixed $value
	 */
	public function __construct( $value ) {

		if ( is_object( $value ) ) {
			throw new \InvalidArgumentException( "Only primitive types allowed." );
		}

		$this->value = $value;
	}

	/**
	 * The __toString method allows a class to decide how it will react when it
	 * is converted to a string.
	 *
	 * @return string
	 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
	 */
	public function __toString() {
		return (string) $this->value;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize( $this->value );
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 */
	public function unserialize( $serialized ) {
		$this->value = unserialize( $serialized );
	}
}