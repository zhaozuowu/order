<?php
/**
 * @name Action_BatchFinishPickupOrder
 * @desc 仓库批量完成拣货
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_BatchFinishPickupOrder extends Order_Base_Action
{

    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockout_order_ids' => 'str|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockout_BatchFinishPickupOrder();

        
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $res = ['failPickupNum'=>$data['failPickupNum'],'successPickNum'=>$data['successPickNum']];
        if (!empty($data['failPickupNum'])) {
            $res['message'] = $data['failPickupNum'].'单批量操作失败，请您刷新后重试，建议按单个出库单进行拣货操作。';
        }
        return $res;
    }

}