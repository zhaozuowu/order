<?php
/**
 * @desc 查询上架单列表
 * @date 2018/5/4
 * @author 张雨星(yuxing.zhang@ele.me)
 */

class Service_Page_Place_GetPlaceOrderList implements Order_Base_Page
{
    /**
     * @var Service_Data_Reserve_ReserveOrder
     */
    private $objDataPlaceOrder;

    /**
     * Service_Page_Reserve_CreateReserveOrder constructor.
     */
    function __construct()
    {
        $this->objDataPlaceOrder = new Service_Data_PlaceOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrInput['source_order_id'] = ltrim($arrInput['source_order_id'], Nscm_Define_OrderPrefix::SIO);
        $arrInput['warehouse_ids'] = explode(',', $arrInput['warehouse_ids']);
        $arrRet = $this->objDataPlaceOrder->getPlaceOrderList($arrInput);
        return $arrRet;
    }
}