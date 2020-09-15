<?php


namespace SergeLiatko\HTMLTable\Interfaces;

/**
 * Interface Table
 *
 * @package SergeLiatko\HTMLTable\Interfaces
 */
interface Table {

	/**
	 * Table constructor.
	 *
	 * @param mixed[]              $args {
	 *
	 * Associative array of arguments:
	 *
	 * @type array[]|object[]      $items
	 * @type string[]              $columns
	 * @type callable[]            $callbacks
	 * @type false|string|callable $show_header
	 * @type false|string|callable $show_footer
	 * @type string[]              $table_attrs
	 * @type string[]              $row_attrs
	 * @type string[]              $cell_attrs
	 * @type string[]              $header_cols
	 * }
	 */
	public function __construct( array $args );

	/**
	 * @param mixed[] $args
	 *
	 * @return string Table HTML code.
	 * @see \SergeLiatko\HTMLTable\Interfaces\Table::__construct() for parameters
	 */
	public static function HTML( array $args ): string;

	/**
	 * @return string Table HTML code.
	 */
	public function toHTML(): string;

}
