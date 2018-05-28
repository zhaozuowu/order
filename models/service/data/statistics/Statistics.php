<?php
/**
 * @name Service_Data_Statistics_Statistics
 * @desc 统计
 * @author lvbochao@iwaimai.baidu.com
 */

class Service_Data_Statistics_Statistics
{
    /**
     * @param $intOrderId
     * @param $intType
     * @throws Order_BusinessError
     * @throws Order_Error
     * @throws Nscm_Exception_Error
     */
    public function addOrderStatistics($intOrderId, $intType)
    {
        $arrDb = $this->getDbRow($intOrderId, $intType);
        if (empty($arrDb)) {
            Bd_Log::warning(sprintf('STATISTICS_INSERT_NOT_FOUND, info: order_id[%d], type[%d]', $intOrderId, $intType));
            if (Order_Statistics_Type::TABLE_STOCKIN_STOCKOUT == $intType) {
                Bd_Log::warning('STATISTICS_STOCKIN_STOCKOUT_INSERT_NOT_FOUND: ' . $intOrderId);
                return;
            }
            Order_Error::throwException(Order_Error_Code::CONNECT_MYSQL_FAILED);
        }
        /**
         * @var Order_Base_Orm|string $strOrm
         */
        $strOrm = Order_Statistics_Table::ORM_DIST[$intType];
        if (is_callable([$strOrm, 'batchInsert'])) {
            $strOrm::batchInsert($arrDb);
        } else {
            Bd_Log::warning($strOrm . ' is not instanceof Order_Base_Orm');
            Order_Error::throwException(Order_Error_Code::CONFIG_ERROR);
        }
    }

    /**
     * @param int $intOrderId
     * @param int $intType
     * @throws Order_BusinessError
     * @throws Order_Error
     * @throws Nscm_Exception_Error
     */
    public function updateOrderStatistics($intOrderId, $intType)
    {
        $arrDb = $this->getDbRow($intOrderId, $intType, false);
        /**
         * @var Order_Base_Orm|string $strOrm
         */
        $strOrm = Order_Statistics_Table::ORM_DIST[$intType];
        $strSlaveKey = Order_Statistics_Table::ORM_TABLE[$intType]['slave_key'];
        $strSlaveSku = Order_Statistics_Table::ORM_TABLE[$intType]['slave_sku'];
        if (is_callable($strOrm . '::update')
            && is_callable($strOrm . '::findAll')) {
            foreach ($arrDb as $arrRow) {
                $arrCondition = [
                    $strSlaveKey => $arrRow[$strSlaveKey],
                    $strSlaveSku => $arrRow[$strSlaveSku],
                ];
                $objOrms = $strOrm::findAll($arrCondition);
                /**
                 * @var Order_Base_Orm $objOrm
                 */
                foreach ($objOrms as $objOrm) {
                    $boolChanged = false;
                    foreach ($arrRow as $field => $value) {
                        if (isset($objOrm->$field) && !isset($arrCondition[$field]) && $objOrm->$field != $value) {
                            $objOrm->$field = $value;
                            $boolChanged = true;
                        }
                    }
                    if (!$boolChanged) {
                        Bd_Log::warning(sprintf('STATISTICS_UPDATE_NOT_CHANGE, info: order_id[%d], type[%d]',
                            $intOrderId, $intType));
                        continue;
                    }
                    $objOrm->update();
                }
            }
        } else {
            Bd_Log::warning($strOrm . ' is not instanceof Order_Base_Orm');
            Order_Error::throwException(Order_Error_Code::CONFIG_ERROR);
        }
    }

    /**
     * @param int $intOrderId
     * @param int $intType
     * @param bool $boolSplit
     * @return array
     * @throws Order_Error
     * @throws Nscm_Exception_Error
     */
    private function getDbRow($intOrderId, $intType, $boolSplit = true)
    {
        $arrMaster = $this->getMaster($intOrderId, $intType);
        $arrSlave = $this->getSlave($intOrderId, $intType);
        $arrSkuList = $this->getSkuListFromSlave($arrSlave);
        $arrSkus = $this->getSkus($arrSkuList);
        $arrDb = $this->assemble($arrMaster, $arrSlave, $arrSkus, $intType, $boolSplit);
        return $arrDb;
    }

    /**
     * get value
     * @param $arrSourceRow
     * @param $arrColumns
     * @return array
     */
    private function getValue($arrSourceRow, $arrColumns)
    {
        $arrTemp = [];
        foreach ($arrColumns as $key => $value) {
            if (is_int($key)) {
                $mixValue = $arrSourceRow[$value] ?? '';
                $arrTemp[$value] = (null === $mixValue ? '' : $mixValue);
            } else if (is_string($value)) {
                $mixValue = $arrSourceRow[$value] ?? '';
                $arrTemp[$key] = (null === $mixValue ? '' : $mixValue);
            } else if (is_array($value)) {
                switch ($value['type']) {
                    case Order_Statistics_Type::FUNCTION:
                        $arrParams = [];
                        foreach ($value['params'] as $strParam) {
                            if (Order_Statistics_Column::REPLACE == $strParam) {
                                $arrParams[] = $arrSourceRow[$value['replace']];
                            }  else {
                                $arrParams[] = $strParam;
                            }
                        }
                        $mixValue = call_user_func_array($value['function'], $arrParams);
                        $arrTemp[$key] = (null === $mixValue ? '' : $mixValue);
                        break;
                    case Order_Statistics_Type::ARRAY:
                        $mixValue = constant($value['array'])[$arrSourceRow[$value['replace']]];
                        $arrTemp[$key] = (null === $mixValue ? '' : $mixValue);
                        break;
                    case Order_Statistics_Type::FUNCTION_ARRAY:
                        $arrParams = [];
                        foreach ($value['params'] as $strParam) {
                            if (Order_Statistics_Column::REPLACE == $strParam) {
                                $arrParams[] = $arrSourceRow[$value['replace']];
                            }  else {
                                $arrParams[] = $strParam;
                            }
                        }
                        $mixValue = call_user_func_array($value['function'], $arrParams)[$value['key']];
                        $arrTemp[$key] = (null === $mixValue ? '' : $mixValue);
                        break;
                    case Order_Statistics_Type::JSON:
                        $mixValue = json_decode($arrSourceRow[$value['replace']], true)[$value['key']];
                        $arrTemp[$key] = (null === $mixValue ? '' : $mixValue);
                        break;
                }
            }
        }
        return $arrTemp;
    }

    /**
     * get split value
     * @param $arrSourceRow
     * @param $arrColumns
     * @param $arrSemiRow
     * @return array
     */
    private function getSplitValue($arrSourceRow, $arrColumns, $arrSemiRow)
    {
        $arrRes = [];
        foreach ($arrSourceRow as $row) {
            $arrTemp = [];
            foreach ($arrColumns as $key => $value)
            {
                if (is_int($key)) {
                    $arrTemp[$value] = $row[$value];
                } else if (is_string($value)) {
                    $arrTemp[$key] = $row[$value];
                } else if (is_array($value)) {
                    $arrTemp[$key] = $row[$value[0]] * $arrSemiRow[$value[1]];
                }
            }
            $arrRes[] = $arrTemp;
        }
        return $arrRes;
    }

    /**
     * assemble array
     * @param array $arrMaster
     * @param array $arrSlave
     * @param array $arrSku
     * @param int $intType
     * @param bool $boolSplit
     * @return array
     * @throws Order_Error
     */
    private function assemble($arrMaster, $arrSlave, $arrSku, $intType, $boolSplit = true)
    {
        $arrColumns = $this->getColumns($intType);
        $arrRes = [];
        foreach ($arrSlave as $arrRow) {
            $arrTempMaster = $this->getValue($arrMaster, $arrColumns['master']);
            $arrTempSlave = $this->getValue($arrRow, $arrColumns['slave']);
            $arrTempSku = $this->getValue($arrSku[$arrRow['sku_id']], $arrColumns['sku']);
            if (isset($arrColumns['split']) && $boolSplit) {
                $strJsonSplit = $arrRow[$arrColumns['split']['key']];
                $arrTempDetail = $this->getSplitValue(json_decode($strJsonSplit, true),
                    $arrColumns['split']['columns'], $arrTempSlave);
                foreach ($arrTempDetail as $row) {
                    $arrRes[] = array_merge($arrTempMaster, $arrTempSlave, $arrTempSku, $row);
                }
            } else {
                $arrRes[] = array_merge($arrTempMaster, $arrTempSlave, $arrTempSku);
            }
        }
        return $arrRes;
    }

    /**
     * get columns
     * @param int $intTableType
     * @return array
     * @throws Order_Error
     */
    private function getColumns($intTableType) {
        $strConst = 'Order_Statistics_Column::' . Order_Statistics_Type::TABLE_MAP[$intTableType];
        if (!defined($strConst)) {
            Order_Error::throwException(Order_Error_Code::TABLE_NOT_EXIST);
        }
        return constant($strConst);
    }

    /**
     * get skus info
     * @param array $arrSkus as $sku_id
     * @return  array
     * @throws Nscm_Exception_Error
     */
    private function getSkus($arrSkus)
    {
        $arrRes = [];
        $_daoSku = new Dao_Ral_Sku();
        foreach ($arrSkus as $intSkuId) {
            if (!isset($arrRes[$intSkuId])) {
                $arrRet = $_daoSku->getSkuList(1, $intSkuId)['skus'][0];
                $arrRes[$intSkuId] = $arrRet;
            }
        }
        return $arrRes;
    }

    /**
     * get master info
     * @param int $intSourceId
     * @param int $intType
     * @return array
     * @throws Order_Error
     */
    private function getMaster($intSourceId, $intType)
    {
        $arrOrm = Order_Statistics_Table::ORM_TABLE[$intType];
        if (empty($arrOrm)) {
            Order_Error::throwException(Order_Error_Code::ORM_NOT_EXIST);
        }
        $arrCondition = [
            $arrOrm['master_key'] => $intSourceId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        /**
         * @var Order_Base_Orm $strClass
         */
        $strClass = $arrOrm['master'];
        $arrRes =$strClass::findRow($strClass::getAllColumns(), $arrCondition);
        return $arrRes;
    }

    /**
     * get slave info
     * @param int $intSourceId
     * @param int $intType
     * @return array
     * @throws Order_Error
     */
    private function getSlave($intSourceId, $intType)
    {
        $arrOrm = Order_Statistics_Table::ORM_TABLE[$intType];
        if (empty($arrOrm)) {
            Order_Error::throwException(Order_Error_Code::ORM_NOT_EXIST);
        }
        $arrCondition = [
            $arrOrm['slave_key'] => $intSourceId,
            'is_delete' => Order_Define_Const::NOT_DELETE,
        ];
        /**
         * @var Order_Base_Orm $strClass
         */
        $strClass = $arrOrm['slave'];
        $arrRes =$strClass::findRows($strClass::getAllColumns(), $arrCondition);
        return $arrRes;
    }

    /**
     * get sku list from slave
     * @param array $arrSlave
     * @return array
     */
    private function getSkuListFromSlave($arrSlave)
    {
        $arrRet = [];
        foreach ($arrSlave as $row) {
            $arrRet[] = $row['sku_id'];
        }
        return $arrRet;
    }
}