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
    private $objDataPlace;

    /**
     * Service_Page_Reserve_CreateReserveOrder constructor.
     */
    function __construct()
    {
        $this->objDataPlace = new Service_Data_Place_PlaceOrder();
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws Order_BusinessError
     */
    public function execute($arrInput)
    {
        $arrRet = $this->objDataPlace->getPlaceOrderStatistics();
        return $arrRet;
    }
}