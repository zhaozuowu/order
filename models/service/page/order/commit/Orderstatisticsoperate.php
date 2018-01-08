<?php
/**
 * @name Service_Page_Order_Commit_Orderstatisticsoperate
 * @desc 订单统计操作
 * @author lvbochao@iwaimai.baidu.com
 */
class Service_Page_Order_Commit_Orderstatisticsoperate extends Wm_Lib_Wmq_CommitPageService
{
    /**
     * @var Service_Data_Statistics_Statistics $objData
     */
    private $objData;

    /**
     * constructor
     */
    public function beforeExecute()
    {
        parent::beforeExecute();
        $this->objData = new Service_Data_Statistics_Statistics();
    }

    /**
     *
     * @param array $arrRequest <p>
     * type: 类型，1-新增，2-修改
     * table：表，1-采购入库，2-销退入库，3-出库
     * key：主键，即单号，不包含字母
     * </p>
     * @throws Order_BusinessError
     * @throws Order_Error
     * @throws Nscm_Exception_Error
     */
    public function myExecute($arrRequest)
    {
        Bd_Log::debug(__METHOD__ . ' request params: ' . json_encode($arrRequest));
        switch (intval($arrRequest['type'])) {
            case Order_Statistics_Type::ACTION_CREATE:
                $this->objData->addOrderStatistics($arrRequest['key'], $arrRequest['table']);
                break;
            case Order_Statistics_Type::ACTION_UPDATE:
                $this->objData->updateOrderStatistics($arrRequest['key'], $arrRequest['table']);
                break;
            default:
                Bd_Log::warning('type error! input type: ' . $arrRequest['type']);
        }
    }
}