<?php
/**
 * @name Controller_BusinessService
 * @desc 创建业态订单
 * @author  jinyu02@iwaimai.baidu.com
 */
class Controller_BusinessService extends Nscm_Base_ControllerService {

    /**
     * 地址映射
     * @var array
     */
    public $arrMap = [
        'Action_Service_CreateBusinessFormOrder' => 'actions/service/CreateBusinessFormOrder.php',
    ];

    /**
     * 创建业态订单
     * @param $arrRequest
     * @return array
     */
    public function createBusinessFormOrder($arrRequest) {
        $arrRequest = $arrRequest['objBusinessFormOrderInfo'];
        $objAction = new Action_Service_CreateBusinessFormOrder($arrRequest);
        return $objAction->execute();
    }
}
