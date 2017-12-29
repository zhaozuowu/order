<?php
/**
 * @name Action_GetSkuList
 * @desc get sku list
 * @author wanggang01@iwaimai.baidu.com
 */

class Action_GetSkuList extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'page_num' => 'int|default[1]',
        'page_size' => 'int|required|max[100]',
        'sku_id' => 'str',
        'upc_id' => 'str',
        'sku_name' => 'str',
        'sku_category_1' => 'int',
        'sku_category_2' => 'int',
        'sku_category_3' => 'int',
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
        $this->objPage = new Service_Page_Sku_GetSkuList();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}
