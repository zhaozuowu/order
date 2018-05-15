<?php
/**
 * @name GetPickupOrderPrint.php
 * @desc
 * @author: bochao.lv@ele.me
 * @createtime: 2018/5/12 16:23
 */

class Action_GetPickupOrderPrint extends Order_Base_Action
{

    protected $arrInputParams = [
        'pickup_order_ids' => 'regex|patern[/^\d+(\,\d+)*$/]',
    ];

    protected $intMethod = Order_Define_Const::METHOD_GET;

    /**
     * 格式化输出
     *
     * @param  array $data
     * @return array
     */
    public function format($data)
    {
        $arrRet = [];
        if (empty($data)) {
            return $arrRet;
        }
        foreach ($data as $row) {
            if (!isset($arrRet[$row['sku_id']])) {
                $arrRet[$row['sku_id']] = [
                    'upc_id' => $row['upc_id'],
                    'sku_name' => $row['sku_name'],
                    'sku_net' => $row['sku_net'],
                    'sku_net_unit_text' => Order_Define_Sku::SKU_NET_MAP[$row['sku_net_unit']],
                    'upc_unit_text' => Order_Define_Sku::UPC_UNIT_MAP[$row['upc_unit']],
                    'sku_detail' => [],
                ];
            }
            $arrRet[$row['sku_id']]['sku_detail'][] = [
                // @todo columns
                // @zuowu.zhao@ele.me
            ];
        }
        ksort($arrRet);
        $arrRet = array_values($arrRet);
        return $arrRet;
    }

    /**
     * constructor
     * @return mixed
     */
    function myConstruct()
    {
        $this->objPage = new Service_Page_Pickup_GetPickupOrderPrint();
    }
}