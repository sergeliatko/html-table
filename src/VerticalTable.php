<?php


namespace SergeLiatko\HTMLTable;

use SergeLiatko\HTML\Table as TableHTML;
use SergeLiatko\HTML\Tbody;
use SergeLiatko\HTML\Td;
use SergeLiatko\HTML\Th;
use SergeLiatko\HTML\Tr;
use SergeLiatko\HTMLTable\Traits\ParseArgsRecursive;

/**
 * Class VerticalTable
 *
 * @package SergeLiatko\HTMLTable
 */
class VerticalTable implements Interfaces\Table {

	use Traits\Table, ParseArgsRecursive;

	/**
	 * @var array[]|mixed[]|object[]|array $items Items to present in the table.
	 */
	protected $items;

	/**
	 * @var string[]|array $columns Table headers (as associative array column=>title).
	 */
	protected $columns;

	/**
	 * @var callable[]|array $callbacks Callbacks to use for each column (as associative array column=>callback).
	 */
	protected $callbacks;

	/**
	 * @var string[]|array $table_attrs
	 */
	protected $table_attrs;

	/**
	 * @var string[]|array $row_attrs
	 */
	protected $row_attrs;

	/**
	 * @var string[]|array $cell_attrs
	 */
	protected $cell_attrs;

	/**
	 * @var string[]|array $header_cols
	 */
	protected $header_cols;

	/**
	 * VerticalTable constructor.
	 *
	 * @param mixed[]|array $args
	 */
	public function __construct( array $args ) {
		/**
		 * @var array[]|mixed[]|object[]|array $items
		 * @var string[]|array                 $columns
		 * @var callable[]|array               $callbacks
		 * @var string[]|array                 $table_attrs
		 * @var string[]|array                 $row_attrs
		 * @var string[]|array                 $cell_attrs
		 * @var string[]|array                 $header_cols
		 */
		extract( self::parseArgsRecursive( $args, $this->getDefaultArgs() ) );

		$this->setItems( $items );
		$this->setColumns( $columns );
		$this->setCallbacks( $callbacks );
		$this->setTableAttrs( $table_attrs );
		$this->setRowAttrs( $row_attrs );
		$this->setCellAttrs( $cell_attrs );
		$this->setHeaderCols( $header_cols );
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->toHTML();
	}

	/**
	 * @inheritDoc
	 */
	public function toHTML(): string {
		return TableHTML::HTML(
			$this->getTableAttrs(),
			$this->getTableBody()
		);
	}

	/**
	 * @return string
	 */
	public function getTableBody(): string {
		return Tbody::HTML(
			array(),
			$this->getTableRows()
		);
	}

	/**
	 * @return string
	 */
	public function getTableRows(): string {
		$html = '';
		foreach ( array_keys( array_filter( $this->getColumns() ) ) as $column ) {
			$html .= $this->getTableRow( $column );
		}

		return $html;
	}

	/**
	 * @param string $column
	 *
	 * @return string
	 */
	public function getTableRow( string $column ): string {
		$cells     = $this->getColumnHeader( $column );
		$index     = 1;
		$is_header = $this->isHeaderCol( $column );
		foreach ( $this->getItems() as $item ) {
			$cells .= $this->getCell( $item, $column, $index, $is_header );
			$index ++;
		}

		return Tr::HTML(
			self::formatAttributes( $this->getRowAttrs(), $column ),
			$cells
		);
	}

	/**
	 * @param mixed|array|object $item
	 * @param string             $column
	 * @param int                $index
	 * @param bool               $header
	 *
	 * @return string
	 */
	public function getCell( $item, string $column, int $index, bool $header = false ): string {
		return empty( $header ) ?
			Td::HTML(
				self::formatAttributes( $this->getCellAttrs(), $column, $index ),
				$this->getCellContent( $item, $column, $index )
			)
			: Th::HTML(
				self::formatAttributes( $this->getCellAttrs(), $column, $index ),
				$this->getCellContent( $item, $column, $index )
			);
	}

	/**
	 * @param array|mixed|object $item
	 * @param string             $column
	 * @param int                $index
	 *
	 * @return string
	 */
	public function getCellContent( $item, string $column, int $index = 0 ): string {
		return is_callable( $callback = $this->getColumnCallback( $column ) ) ?
			(string) call_user_func_array( $callback, array( $item, $column, $index ) )
			: '';
	}

	/**
	 * @param string $column
	 *
	 * @return string
	 */
	public function getColumnHeader( string $column ): string {
		return Th::HTML(
			self::formatAttributes( $this->getCellAttrs(), $column ),
			self::returnItemKeyValue( $this->getColumns(), $column )
		);
	}

	/**
	 * @return array|array[]|mixed[]|object[]
	 */
	public function getItems(): array {
		return $this->items;
	}

	/**
	 * @param array|array[]|mixed[]|object[] $items
	 *
	 * @return VerticalTable
	 */
	public function setItems( array $items ): VerticalTable {
		$this->items = $items;

		return $this;
	}

	/**
	 * @return array|string[]
	 */
	public function getColumns(): array {
		return $this->columns;
	}

	/**
	 * @param array|string[] $columns
	 *
	 * @return VerticalTable
	 */
	public function setColumns( array $columns ): VerticalTable {
		$this->columns = $columns;

		return $this;
	}

	/**
	 * @return array|callable[]
	 */
	public function getCallbacks(): array {
		return $this->callbacks;
	}

	/**
	 * @param array|callable[] $callbacks
	 *
	 * @return VerticalTable
	 */
	public function setCallbacks( array $callbacks ): VerticalTable {
		$this->callbacks = $callbacks;

		return $this;
	}

	/**
	 * @return array|string[]
	 */
	public function getTableAttrs(): array {
		return $this->table_attrs;
	}

	/**
	 * @param array|string[] $table_attrs
	 *
	 * @return VerticalTable
	 */
	public function setTableAttrs( array $table_attrs ): VerticalTable {
		$this->table_attrs = $table_attrs;

		return $this;
	}

	/**
	 * @return array|string[]
	 */
	public function getRowAttrs(): array {
		return $this->row_attrs;
	}

	/**
	 * @param array|string[] $row_attrs
	 *
	 * @return VerticalTable
	 */
	public function setRowAttrs( array $row_attrs ): VerticalTable {
		$this->row_attrs = $row_attrs;

		return $this;
	}

	/**
	 * @return array|string[]
	 */
	public function getCellAttrs(): array {
		return $this->cell_attrs;
	}

	/**
	 * @param array|string[] $cell_attrs
	 *
	 * @return VerticalTable
	 */
	public function setCellAttrs( array $cell_attrs ): VerticalTable {
		$this->cell_attrs = $cell_attrs;

		return $this;
	}

	/**
	 * @return array|string[]
	 */
	public function getHeaderCols(): array {
		return $this->header_cols;
	}

	/**
	 * @param array|string[] $header_cols
	 *
	 * @return VerticalTable
	 */
	public function setHeaderCols( array $header_cols ): VerticalTable {
		$this->header_cols = $header_cols;

		return $this;
	}

	/**
	 * @param string $column
	 *
	 * @return bool
	 */
	public function isHeaderCol( string $column ): bool {
		return in_array( $column, $this->getHeaderCols() );
	}

	/**
	 * @return array[]
	 */
	protected function getDefaultArgs(): array {
		return array(
			'items'       => array(),
			'columns'     => array(),
			'callbacks'   => array(),
			'table_attrs' => array(),
			'row_attrs'   => array(),
			'cell_attrs'  => array(),
			'header_cols' => array(),
		);
	}

}
