<?php
/**
 * @name Action_Service_CreateBusinessFormOrder
 * @desc Action_Service_CreateBusinessFormOrder
 * @author jinyu02@iwaimai.baidu.com
 */
class Action_Service_CreateBusinessFormOrder extends Order_Base_ServiceAction {
	/**
	 * input params
	 * @var array
	 */
	protected $arrInputParams = [
	    'logistics_order_id' => 'str|required',
		'business_form_order_type' => 'int|required',
		'shelf_info' => 'arr|required',
		'business_form_order_remark' => 'str|len[128]',
		'customer_id' => 'str|required|len[32]',
		'customer_name' => 'str|required|len[128]',
		'customer_contactor' => 'str|required|len[32]',
		'customer_contact' => 'str|len[25]',
		'customer_address' => 'str|required|len[255]',
		'customer_location' => 'str|required|len[128]',
		'customer_location_source' => 'int|required',
		'customer_city_id' => 'int|required',
		'customer_city_name' => 'str|required|len[32]',
		'customer_region_id' => 'int|required',
		'customer_region_name' => 'str|required|len[32]',
		'executor' => 'str|required|max[32]',
        'executor_contact' => 'str|required|len[11]|min[11]',
        'expect_arrive_time' => [
			'validate' => 'arr|required',
			'type' => 'map',
			'params' => [
				'start' => 'int|required',
				'end' => 'int|required',
			],
		],
		'skus' => [
			'validate' => 'arr|required',
			'type' => 'array',
			'params' => [
				'sku_id' => 'int|required',
				'order_amount' => 'int|required|min[1]',
			],
		],
	];

	/**
	 * method
	 * @var int
	 */
	protected $intMethod = Order_Define_Const::METHOD_POST;

	/**
	 * init object
	 */
	public function myConstruct() {
		$this->objPage = new Service_Page_Business_CreateBusinessFormOrder();
	}

	/**
	 * format result
	 * @param array $arrRet
	 * @return array
	 */
	public function format($arrRet) {
	    $arrFormatRet = [];
	    if (empty($arrRet)) {
	        return $arrFormatRet;
        }
        $arrFormatRet['stockout_order_id'] = empty($arrRet['stockout_order_id']) ? 0 : $arrRet['stockout_order_id'];
	    $arrFormatRet['skus'] = $this->formatSkus($arrRet['skus']);
		return $arrFormatRet;
	}

    /**
     * format skus
     * @param $arrSkus
     * @return array
     */
	public function formatSkus($arrSkus) {
	    $arrFormatSkus = [];
        if (empty($arrSkus)) {
            return $arrFormatSkus;
        }
        foreach ((array)$arrSkus as $arrSkuItem) {
            $arrFormatSkuItem = [];
            $arrFormatSkuItem['sku_id'] = empty($arrSkuItem['sku_id']) ? 0 : $arrSkuItem['sku_id'];
            $arrFormatSkuItem['cost_price_tax'] = empty($arrSkuItem['cost_price_tax']) ?
                                                    0 : $arrSkuItem['cost_price_tax'];
            $arrFormatSkuItem['cost_price_untax'] = empty($arrSkuItem['cost_price']) ?
                                                    0 : $arrSkuItem['cost_price'];
            $arrFormatSkuItem['order_amount'] = empty($arrSkuItem['order_amount']) ?
                                                    0 : $arrSkuItem['order_amount'];
            $arrFormatSkuItem['distribute_amount'] = empty($arrSkuItem['distribute_amount']) ?
                                                    0 : $arrSkuItem['distribute_amount'];
            $arrFormatSkus[] = $arrFormatSkuItem;
        }
        return $arrFormatSkus;

    }
}
