<?php

namespace Give\TestData\Framework;

class MetaRepository
{

    /** @var string */
    protected $tableName;

    /** @var string */
    protected $relationshipColumnName;

    /**
     * @param string $relationshipColumnName
     */
    public function __construct($tableName, $relationshipColumnName)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix . $tableName;
        $this->relationshipColumnName = $relationshipColumnName;
    }

    /**
     * @since TBD Prepare each row with placeholders.
     */
    public function persist($relationshipID, $metaData)
    {
        $values = array_map(
            function ($metaKey, $metaValue) use ($relationshipID) {
                return $this->wpdb->prepare('( %d, %s, %s )', $relationshipID, $metaKey, $metaValue);
            },
            array_keys($metaData),
            $metaData
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- rows are prepared above; getSql() adds the literal table and column names.
        $this->wpdb->query($this->getSql($values));
    }

    protected function getSql($values)
    {
        $format = "INSERT INTO $this->tableName {$this->getColumns()} VALUES %s";

        return sprintf($format, implode(',', $values));
    }

    protected function getColumns()
    {
        return sprintf('( %s, meta_key, meta_value )', $this->relationshipColumnName);
    }
}
