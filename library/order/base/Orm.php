<?php
/**
 * @name Model_Orm_OrderBase
 * @desc Model_Orm_OrderBase
 * @author lvbochao@iwaimai.baidu.com
 */

Class Order_Base_Orm extends Wm_Orm_ActiveRecord{

    /**
     * get all columns
     * @return array
     */
    public static function getAllColumns()
    {
        return array_keys(static::getColumnsDefine());
    }

    /** insert
     * @param $row
     * @param bool $ignore
     * @param array $onDuplicateUpdateFields
     * @return int
     */
    public static function insert($row, $ignore = false, $onDuplicateUpdateFields = [])
    {
        $intTime = time();
        if (!isset($row['is_delete'])) {
            $row['is_delete'] = Order_Define_Const::NOT_DELETE;
        }
        if (!isset($row['create_time'])) {
            $row['create_time'] = $intTime;
        }
        if (!isset($row['update_time'])) {
            $row['update_time'] = $intTime;
        }
        if (!isset($row['version'])) {
            $row['version'] = 0;
        }
        return parent::insert($row, $ignore, $onDuplicateUpdateFields);
    }

    /**
     * batch insert
     * @param array $rows
     * @param bool $ignore
     * @param array $onDuplicateUpdateFields
     * @return int
     */
    public static function batchInsert(array $rows, $ignore = false, $onDuplicateUpdateFields = [])
    {
        $arrNewRows = [];
        $intTime = time();
        foreach ($rows as $row) {
            if (!isset($row['is_delete'])) {
                $row['is_delete'] = Order_Define_Const::NOT_DELETE;
            }
            if (!isset($row['create_time'])) {
                $row['create_time'] = $intTime;
            }
            if (!isset($row['update_time'])) {
                $row['update_time'] = $intTime;
            }
            if (!isset($row['version'])) {
                $row['version'] = 0;
            }
            $arrNewRows[] = $row;
        }
        return parent::batchInsert($arrNewRows, $ignore, $onDuplicateUpdateFields);
    }

    /**
     * update
     * @param array $fields
     * @param array $cond
     * @return bool
     */
    public function update(array $fields = [], $cond = [])
    {
        if (!isset($this->_dirtyFields['version'])) {
            $this->version++;
        }
        if (!isset($this->_dirtyFields['update_time'])) {
            $this->update_time = time();
        }
        return parent::update($fields, $cond); // TODO: Change the autogenerated stub
    }

    /**
     * find rows and total count
     * 版权没有，盗版不究
     * @param $columns
     * @param $cond
     * @param array $orderBy
     * @param int $offset
     * @param null $limit
     * @return array
     */
    public static function findRowsAndTotalCount($columns, $cond, $orderBy = [], $offset = 0, $limit = null)
    {
        $option = 'SQL_CALC_FOUND_ROWS';
        $arrRows = static::find($cond)->orderBy($orderBy)->offset($offset)->limit($limit)->select($columns, $option)->rows();
        $intCount = static::find([])->select(['found_rows() as sfr'])->row()['sfr'];
        $arrRet = [
            'rows' => $arrRows,
            'total' => intval($intCount),
        ];
        return $arrRet;
    }
}