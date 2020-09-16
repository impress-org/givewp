<?php
namespace Give\Framework;

use Give\Framework\Database\Table;
use http\Exception\InvalidArgumentException;
use wpdb;

/**
 * Class Table
 * @package Give\Database\Tables
 *
 * @since 2.9.0
 */
abstract class TableAccessor {
	/**
	 * @since 2.9.0
	 * @var Table
	 */
	protected $table;

	/**
	 * @since 2.9.0
	 * @var wpdb
	 */
	private $db;

	/**
	 * Table constructor.
	 *
	 * @param  Table  $table
	 */
	public function __construct( Table $table ) {
		$this->table = $table;
		$this->db    = $this->table->getDb();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  int $primaryKeyValue Primary key value.
	 *
	 * @return object
	 */
	public function get( $primaryKeyValue ) {
		return $this->db->get_row(
			$this->db->prepare("
					SELECT * FROM {$this->table->getName()}
					WHERE {$this->table->getPrimaryKey()} = %s LIMIT 1;
				",
				$primaryKeyValue
			)
		);
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  int $column Column ID.
	 * @param  int $columnValue Row ID.
	 *
	 * @return object
	 */
	public function getBy( $column, $columnValue ) {
		$this->validateColumn( $column );

		return $this->db->get_row(
			$this->db->prepare(
				"
			SELECT * FROM {$this->table->getName()}
			WHERE {$column} = %s
			LIMIT 1;",
				$columnValue
			)
		);
	}

	/**
	 * Retrieve all rows by a specific column / value
	 * Note: currently support string comparison
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param array $columnArgs Array contains column key and expected value.
	 *
	 * @return array
	 */
	public function getResultsBy( $columnArgs ) {
		$this->validateColumns( $columnArgs );

		$columnArgs = wp_parse_args(
			$columnArgs,
			[ 'relation' => 'AND' ]
		);

		$relation = $columnArgs['relation'];
		unset( $columnArgs['relation'] );

		$where = [];
		foreach ( $columnArgs as $name => $value ) {
			$value = esc_sql( $value );
			$name  = esc_sql( $name );

			$where[] = "{$name}='{$value}'";
		}
		$where = implode( " {$relation} ", $where );

		return $this->db->get_results(
			"
			SELECT * FROM {$this->table_name}
			WHERE {$where};"
		);
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  string $column Column ID.
	 * @param  int $primaryKeyValue Row ID.
	 *
	 * @return string      Column value.
	 */
	public function getColumn( $column, $primaryKeyValue ) {
		$this->validateColumn( $column );

		$column = esc_sql( $column );

		return $this->db->get_var(
			$this->db->prepare(
				"
			SELECT {$column}
			FROM {$this->table->getName()}
			WHERE {$this->table->getPrimaryKey()} = %s
			LIMIT 1;",
				$primaryKeyValue
			)
		);
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  string    $column       Column ID.
	 * @param  string $columnWhere Column name.
	 * @param  string $columnValue Column value.
	 *
	 * @return string
	 */
	public function getColumnBy( $column, $columnWhere, $columnValue ) {
		$this->validateColumn( $column );
		$this->validateColumn( $columnWhere );

		$columnWhere = esc_sql( $columnWhere );
		$column      = esc_sql( $column );

		return $this->db->get_var(
			$this->db->prepare(
				"
			SELECT {$column}
			FROM {$this->table->getName()}
			WHERE {$columnWhere} = %s
			LIMIT 1;",
				$columnValue
			)
		);
	}

	/**
	 * Insert a new row
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  array  $data
	 *
	 * @return int
	 */
	public function insert( $data ) {
		// Set default values.
		$data = wp_parse_args( $data, $this->table->get_column_defaults() );

		// Initialise column format array
		$column_formats = $this->table->getColumns();

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$this->db->insert( $this->table->getName(), $data, $column_formats );

		return $this->db->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  int    $rowId Column ID
	 * @param  array  $data
	 * @param  string $where  Column value
	 *
	 * @return int|bool
	 */
	public function update( $rowId, $data = [], $where = '' ) {
		// Row ID must be positive integer
		$rowId = absint( $rowId );

		if ( empty( $rowId ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->table->getPrimaryKey();
		}

		// Initialise column format array
		$column_formats = $this->table->getColumns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		return $this->db->update( $this->table->getName(), $data, [ $where => $rowId ], $column_formats );
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @param  int $rowId Column ID.
	 *
	 * @return int|bool
	 */
	public function delete( $rowId = 0 ) {
		// Row ID must be positive integer
		$rowId = absint( $rowId );

		if ( empty( $rowId ) ) {
			return false;
		}

		return $this->db->query(
			$this->db->prepare(
				"
			DELETE FROM {$this->table->getName()}
			WHERE {$this->table->getPrimaryKey()} = %d",
				$rowId
			)
		);
	}

	/**
	 * Throw an error if column does not exist in database table.
	 *
	 * @since 2.9.0
	 *
	 * @param string $column Table column name.
	 */
	public function validateColumn( $column ) {
		if ( ! array_key_exists( $column, $this->table->getColumns() ) ) {
			throw new InvalidArgumentException( "Column does not exist in {$this->table->getName()}. Please query a valid column." );
		}
	}

	/**
	 * Throw an error if column does not exist in database table.
	 *
	 * @since 2.9.0
	 *
	 * @param $data
	 */
	public function validateColumns( $data ) {
		if ( ! array_diff_key( $data, $this->table->getColumns() ) ) {
			throw new InvalidArgumentException( "Column does not exist in {$this->table->getName()}. Please query a valid column." );
		}
	}
}
