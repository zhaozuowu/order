<?php
/**
 * @name Action_GetPickupOrderCountByWaiting
 * @desc get pickup order count by waiting
 * @author bochao.lv@ele.me
 */

class Action_GetPickupOrderCountByWaiting extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_ids' => 'regex|patern[/^\d+(\,\d+)*$/]',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_GetPickupOrderCountByWaiting();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return [
            'count' => $data,
        ];
    }
}
