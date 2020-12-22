<?php

namespace Give\TestData\Framework;

class MetaRepository {

	/** @var string */
	protected $tableName;

	/** @var string */
	protected $relationshipColumnName;

	/**
	 * @param string $relationshipColumnName
	 */
	public function __construct( $tableName, $relationshipColumnName ) {
		global $wpdb;
		$this->wpdb                   = $wpdb;
		$this->tableName              = $wpdb->prefix . $tableName;
		$this->relationshipColumnName = $relationshipColumnName;
	}

	public function persist( $relationshipID, $metaData ) {

		$values = array_map(
			function ( $metaKey, $metaValue ) use ( $relationshipID ) {
				return sprintf( "( %s, '%s', '%s' )", $relationshipID, esc_sql( $metaKey ), esc_sql( $metaValue ) );
			},
			array_keys( $metaData ),
			$metaData
		);

		$this->wpdb->query(
			$this->getSql( $values )
		);
	}

	protected function getSql( $values ) {
		$format = "INSERT INTO $this->tableName {$this->getColumns()} VALUES %s";

		return sprintf( $format, implode( ',', $values ) );
	}

	protected function getColumns() {
		return sprintf( '( %s, meta_key, meta_value )', $this->relationshipColumnName );
	}
}
