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
        $list = [
            'for_picking_amount'=>0,
            'finish_picking_amount'=>0,
            'cancel_picking_amount'=>0,
        ];
       foreach ($data as $arrPickOrder)
       {
           if($arrPickOrder['pickup_order_status'] == Order_Define_PickupOrder::PICKUP_ORDER_STATUS_INIT){
            $list['for_picking_amount'] = $arrPickOrder['pickupOrderNum'];
           }elseif($arrPickOrder['pickup_order_status'] == Order_Define_PickupOrder::PICKUP_ORDER_STATUS_FINISHED){
               $list['finish_picking_amount'] = $arrPickOrder['pickupOrderNum'];
           }elseif($arrPickOrder['pickup_order_status'] == Order_Define_PickupOrder::PICKUP_ORDER_STATUS_CANCEL){
               $list['cancel_picking_amount'] = $arrPickOrder['pickupOrderNum'];
           }

       }
       return $list;
    }
}
