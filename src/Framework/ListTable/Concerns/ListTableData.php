<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @since 4.0.0
 */
trait ListTableData
{
    /**
     * @var array|object Data provided by the list table
     */
    private $listTableData;

    /**
     * @var bool Define if the column is using list table data
     */
    protected $useData = true;

    /**
     * @since 4.0.0
     * @param array|object $data;
     */
    public function setListTableData($data)
    {
        $this->listTableData = $data;
    }

    /**
     * @since 4.0.0
     * @return array|object
     */
    public function getListTableData()
    {
        return $this->listTableData;
    }


    /**
     * @since 4.0.0
     *
     * @return bool
     */
    public function isUsingListTableData(): bool
    {
        return $this->useData;
    }
}
