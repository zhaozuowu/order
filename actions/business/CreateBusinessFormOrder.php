<?php
/**
 * @name Action_CreateBusinessFormOrder
 * @desc Action_CreateBusinessFormOrder
 * @author jinyu02@iwaimai.baidu.com
 */
class Action_CreateBusinessFormOrder extends Order_Base_ApiAction {

	/**
	 * input params
	 * @var array
	 */
	protected $arrInputParams = [
	    'logistics_order_id' => 'int|required',
		'business_form_order_type' => 'int|required',
		'shelf_info' => 'json|decode|required',
		'business_form_order_remark' => 'str|required|len[255]',
		'customer_id' => 'str|required',
		'customer_name' => 'str|required|len[128]',
		'customer_contactor' => 'str|required|len[32]',
		'customer_contact' => 'str|required',
		'customer_address' => 'str|required|len[255]',
		'customer_location' => 'str|required|len[128]',
		'customer_location_source' => 'int|required',
		'customer_city_id' => 'int|required',
		'customer_city_name' => 'str|required|len[32]',
		'customer_region_id' => 'int|required|min[1]',
		'customer_region_name' => 'str|required|len[32]',
		'executor' => 'str|required|len[32]',
        'executor_contact' => 'str|required|len[11]|min[11]',
        'expect_arrive_time' => [
			'validate' => 'json|decode|required',
			'type' => 'map',
			'params' => [
				'start' => 'int|required',
				'end' => 'int|required',
			],
		],

		'skus' => [
			'validate' => 'json|decode|required',
			'type' => 'array',
			'params' => [
				'sku_id' => 'int|required',
				'order_amount' => 'int|required|min[1]',
			],
		],
        'skus_event' => [
            'validate' => 'json|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int',
                'order_amount' => 'int',
                'event_type'   => 'int'
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
     * execute
     * @return array
     * @throws Exception
     */
	public function myExecute()
    {
        try {
            $this->arrFilterResult['data_source'] = Order_Define_StockoutOrder::STOCKOUT_DATA_SOURCE_OMS;
            return parent::myExecute();
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case Order_Error_Code::NWMS_BUSINESS_FORM_ORDER_CREATE_ERROR:
                    Bd_Log::trace('nwms business form order create error');
                    break;
                case Order_Error_Code::STOCK_FREEZE_ERROR:
                    Bd_Log::warning('nwms stock freeze error');
                default:
                    break;
            }
            throw $e;
        } finally {
            $arrExceptions = Order_Exception_Collector::getExceptionInfo();
            $this->arrData['exceptions'] = $this->formatException($arrExceptions);
        }
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
	    $arrFormatRet['business_form_order_id'] = intval($arrRet['business_form_order_id']);
	    $arrFormatRet['skus'] = $this->formatSkus($arrRet['skus']);
		return $arrFormatRet;
	}

    /**
     * format exception
     * @param array[]
     * @return array[]
     */
	private function formatException($arrExceptions)
    {
        $arrResult = [];
	    foreach ($arrExceptions as $arrException) {
	        $arrResult[] = [
	            'sku_id' => $arrException['sku_id'],
                'exception_info' => $arrException['exception_info'],
                'exception_time' => $arrException['exception_time'],
            ];
        }
        return $arrResult;
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
