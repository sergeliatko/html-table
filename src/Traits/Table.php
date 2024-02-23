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
		$instance = new self( $args );

		return $instance->toHTML();
	}

	/**
	 * @param array|mixed|object $item
	 * @param string             $key
	 *
	 * @return string
	 */
	public static function returnItemKeyValue( $item, string $key ): string {
		$item = (array) $item;

		return isset( $item[ $key ] ) ? strval( $item[ $key ] ) : '';
	}

	/**
	 * @param array  $args
	 * @param string $column
	 * @param int    $index
	 *
	 * @return array
	 */
	public static function formatAttributes( array $args, string $column, int $index = 0 ): array {
		$patterns     = array(
			'{column}',
			'{index}',
		);
		$replacements = array(
			$column,
			$index,
		);
		array_walk( $args, function ( &$value ) use ( $patterns, $replacements ) {
			$value = str_replace( $patterns, $replacements, $value );
		} );

		return $args;
	}

	/**
	 * @param string $column
	 *
	 * @return callable|null
	 */
	public function getColumnCallback( string $column ): ?callable {
		$callbacks = (array) $this->getCallbacks();

		return isset( $callbacks[ $column ] ) ? $callbacks[ $column ] : null;
	}

}
