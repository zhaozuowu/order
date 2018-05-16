<?php
/**
 * @name
 * @desc
 * @author sunzhixin@iwaimai.baidu.com
 */

class Controller_Adjust extends Ap_Controller_Abstract {
    public $actions = array(
        'createincreaseorder' => 'actions/adjust/CreateIncreaseOrder.php', #创建库存调整单-调增#
        'createdecreaseorder' => 'actions/adjust/CreateDecreaseOrder.php', #创建库存调整单-调减#
        'getorder' => 'actions/adjust/GetOrder.php', #查询库存调整单#
        'getorderdetail' => 'actions/adjust/GetOrderDetail.php', #查询单个库存调整单SKU#
        'getorderdetailform' => 'actions/adjust/GetOrderDetailForm.php', #导出页面查询库存调整单SKU#
        'getskustockinfo' => 'actions/adjust/GetSkuStockInfo.php', #查询商品库存信息#
    );
}