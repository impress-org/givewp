<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @unreleased
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
     * @unreleased
     * @param array|object $data;
     */
    public function setListTableData($data)
    {
        $this->listTableData = $data;
    }

    /**
     * @unreleased
     * @return array|object
     */
    public function getListTableData()
    {
        return $this->listTableData;
    }


    /**
     * @unreleased
     *
     * @return bool
     */
    public function isUsingListTableData(): bool
    {
        return $this->useData;
    }
}
