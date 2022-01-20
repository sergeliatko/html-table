<?php


namespace SergeLiatko\HTMLTable;

use SergeLiatko\HTML\Table as TableHTML;
use SergeLiatko\HTML\Tbody;
use SergeLiatko\HTML\Td;
use SergeLiatko\HTML\Tfoot;
use SergeLiatko\HTML\Th;
use SergeLiatko\HTML\Thead;
use SergeLiatko\HTML\Tr;
use SergeLiatko\HTMLTable\Traits\ParseArgsRecursive;

/**
 * Class Table
 *
 * @package SergeLiatko\HTMLTable
 */
class Table implements Interfaces\Table {

	use Traits\Table, ParseArgsRecursive;

	/**
	 * @var array[]|object[] $items Items to present in the table.
	 */
	protected $items;

	/**
	 * @var string[] $columns Table headers (as associative array column=>title).
	 */
	protected $columns;

	/**
	 * @var callable[] $callbacks Callbacks to use for each column (as associative array column=>callback).
	 */
	protected $callbacks;

	/**
	 * @var callable|string|false $show_header
	 */
	protected $show_header;

	/**
	 * @var callable|string|false $show_footer
	 */
	protected $show_footer;

	/**
	 * @var string[] $table_attrs
	 */
	protected $table_attrs;

	/**
	 * @var string[] $row_attrs
	 */
	protected $row_attrs;

	/**
	 * @var string[] $cell_attrs
	 */
	protected $cell_attrs;

	/**
	 * @var string[] $header_cols
	 */
	protected $header_cols;

	/**
	 * @inheritDoc
	 */
	public function __construct( array $args ) {
		/**
		 * @var array[]|object[]      $items
		 * @var string[]              $columns
		 * @var callable[]            $callbacks
		 * @var false|string|callable $show_header
		 * @var false|string|callable $show_footer
		 * @var string[]              $table_attrs
		 * @var string[]              $row_attrs
		 * @var string[]              $cell_attrs
		 * @var string[]              $header_cols
		 */
		extract( self::parseArgsRecursive( $args, $this->getDefaultArgs() ), EXTR_OVERWRITE );

		$this->setItems( $items );
		$this->setColumns( $columns );
		$this->setCallbacks( $callbacks );
		$this->setShowHeader( $show_header );
		$this->setShowFooter( $show_footer );
		$this->setTableAttrs( $table_attrs );
		$this->setRowAttrs( $row_attrs );
		$this->setCellAttrs( $cell_attrs );
		$this->setHeaderCols( $header_cols );
	}

	/**
	 * @param \SergeLiatko\HTMLTable\Table $table
	 * @param int                          $index
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function getHeadersRow( Table $table, int $index = 0 ): string {
		//get column headers
		$headers = $table->getColumns();
		$columns = array_keys( $headers );
		//save current header columns
		$header_cols = $table->getHeaderCols();
		//save current callbacks
		$callbacks = $table->getCallbacks();
		//set new header columns
		$table->setHeaderCols( $columns );
		//set new callbacks
		$table->setCallbacks( array_combine(
			$columns,
			array_fill( 0, count( $columns ), array( get_class( $table ), 'returnItemKeyValue' ) )
		) );
		//get headers row html
		$output = $table->getRow( $headers, $index );
		//restore header cols
		$table->setHeaderCols( $header_cols );
		//restore callbacks
		$table->setCallbacks( $callbacks );

		return $output;
	}

	/**
	 * @return string Table HTML code.
	 */
	public function __toString() {
		return $this->toHTML();
	}

	/**
	 * @return string Table HTML code.
	 */
	public function toHTML(): string {
		return TableHTML::HTML(
			$this->getTableAttrs(),
			join( '', array(
				$this->getTableHeader(),
				$this->getTableBody(),
				$this->getTableFooter(),
			) )
		);
	}

	/**
	 * @return string[]
	 */
	public function getColumns(): array {
		return $this->columns;
	}

	/**
	 * @param string[] $columns
	 *
	 * @return Table
	 */
	public function setColumns( array $columns ): Table {
		$this->columns = $columns;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getHeaderCols(): array {
		return $this->header_cols;
	}

	/**
	 * @param string[] $header_cols
	 *
	 * @return Table
	 */
	public function setHeaderCols( array $header_cols ): Table {
		$this->header_cols = $header_cols;

		return $this;
	}

	/**
	 * @return callable[]
	 */
	public function getCallbacks(): array {
		return $this->callbacks;
	}

	/**
	 * @param callable[] $callbacks
	 *
	 * @return Table
	 */
	public function setCallbacks( array $callbacks ): Table {
		$this->callbacks = array_filter( $callbacks, 'is_callable' );

		return $this;
	}

	/**
	 * @param array|mixed|object $item
	 * @param int                $index
	 *
	 * @return string
	 */
	public function getRow( $item, int $index = 0 ): string {
		$cells = '';
		foreach ( array_keys( array_filter( $this->getColumns() ) ) as $column ) {
			$cells .= $this->getCell( $item, $column, $index );
		}

		return Tr::HTML(
			self::formatAttributes( $this->getRowAttrs(), 'row', $index ),
			$cells
		);
	}

	/**
	 * @param array|mixed|object $item
	 * @param string             $column
	 * @param int                $index
	 *
	 * @return string
	 */
	public function getCell( $item, string $column, int $index = 0 ): string {
		$attributes = self::formatAttributes( $this->getCellAttrs(), $column, $index );
		$content    = $this->getCellContent( $item, $column, $index );

		return $this->isHeaderCol( $column ) ?
			Th::HTML( $attributes, $content )
			: Td::HTML( $attributes, $content );
	}

	/**
	 * @return string[]
	 */
	public function getCellAttrs(): array {
		return $this->cell_attrs;
	}

	/**
	 * @param string[] $cell_attrs
	 *
	 * @return Table
	 */
	public function setCellAttrs( array $cell_attrs ): Table {
		$this->cell_attrs = $cell_attrs;

		return $this;
	}

	/**
	 * @param array|mixed|object $item
	 * @param string             $column
	 * @param int                $index
	 *
	 * @return string
	 */
	public function getCellContent( $item, string $column, int $index = 0 ): string {
		if ( is_callable( $callback = $this->getColumnCallback( $column ) ) ) {
			return (string) call_user_func_array( $callback, array( $item, $column, $index ) );
		}
		$data = (array) $item;

		return ( isset( $data[ $column ] ) && is_scalar( $data[ $column ] ) ) ? strval( $data[ $column ] ) : '';
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
	 * @return string[]
	 */
	public function getRowAttrs(): array {
		return $this->row_attrs;
	}

	/**
	 * @param string[] $row_attrs
	 *
	 * @return Table
	 */
	public function setRowAttrs( array $row_attrs ): Table {
		$this->row_attrs = $row_attrs;

		return $this;
	}

	/**
	 * @return array[]|object[]
	 */
	public function getItems(): array {
		return $this->items;
	}

	/**
	 * @param array[]|object[] $items
	 *
	 * @return Table
	 */
	public function setItems( array $items ): Table {
		$this->items = $items;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTableHeader(): string {
		if ( is_callable( $show_header = $this->getShowHeader() ) ) {
			return Thead::HTML(
				array(),
				(string) call_user_func_array( $show_header, array( $this, 0 ) )
			);
		} elseif ( is_string( $show_header ) ) {
			return Thead::HTML( array(), $show_header );
		}

		return '';
	}

	/**
	 * @return callable|false|string
	 */
	public function getShowHeader() {
		return $this->show_header;
	}

	/**
	 * @param callable|false|string $show_header
	 *
	 * @return Table
	 */
	public function setShowHeader( $show_header ): Table {
		$this->show_header = $show_header;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getTableAttrs(): array {
		return $this->table_attrs;
	}

	/**
	 * @param string[] $table_attrs
	 *
	 * @return Table
	 */
	public function setTableAttrs( array $table_attrs ): Table {
		$this->table_attrs = $table_attrs;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTableFooter(): string {
		if ( is_callable( $show_footer = $this->getShowFooter() ) ) {
			return Tfoot::HTML(
				array(),
				(string) call_user_func_array( $show_footer, array( $this, 0 ) )
			);
		} elseif ( is_string( $show_footer ) ) {
			return Tfoot::HTML( array(), $show_footer );
		}

		return '';
	}

	/**
	 * @return callable|false|string
	 */
	public function getShowFooter() {
		return $this->show_footer;
	}

	/**
	 * @param callable|false|string $show_footer
	 *
	 * @return Table
	 */
	public function setShowFooter( $show_footer ): Table {
		$this->show_footer = $show_footer;

		return $this;
	}

	/**
	 * @param array[]|object[] $items
	 * @param int              $index
	 *
	 * @return string
	 */
	public function getTableRows( array $items, int $index = 1 ): string {
		$output = '';
		foreach ( $items as $item ) {
			$output .= $this->getRow( $item, $index );
			$index ++;
		}

		return $output;
	}

	/**
	 * @return string
	 */
	public function getTableBody(): string {
		return Tbody::HTML(
			array(),
			$this->getTableRows( $this->getItems() )
		);
	}

	/**
	 * @return array
	 */
	protected function getDefaultArgs(): array {
		return array(
			'items'       => array(),
			'columns'     => array(),
			'callbacks'   => array(),
			'show_header' => array( __CLASS__, 'getHeadersRow' ),
			'show_footer' => false,
			'table_attrs' => array(),
			'row_attrs'   => array(),
			'cell_attrs'  => array(),
			'header_cols' => array(),
		);
	}

}
