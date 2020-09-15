<?php


namespace SergeLiatko\HTMLTable\Traits;

/**
 * Trait Table
 *
 * @package SergeLiatko\HTMLTable\Traits
 */
trait Table {

	/**
	 * @param mixed[] $args
	 *
	 * @return string
	 */
	public static function HTML( array $args ): string {
		/** @var \SergeLiatko\HTMLTable\Interfaces\Table $instance */
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$instance = new self( $args );

		return $instance->toHTML();
	}

}
