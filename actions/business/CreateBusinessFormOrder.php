<?php
/**
 * @name Action_CreateBusinessFormOrder
 * @desc Action_CreateBusinessFormOrder
 * @author jinyu02@iwaimai.baidu.com
 */

class Action_CreateBusinessFormOrder extends Order_Base_Action {
	protected $boolCheckLogin = false;
	protected $boolCheckAuth = false;
	/**
	 * input params
	 * @var array
	 */
	protected $arrInputParams = [
		'business_form_order_type' => 'int|required',
		'business_form_order_price' => 'int|required',
		'shelf_info' => 'str|required',
		'business_form_order_remark' => 'str',
		'customer_id' => 'str|required',
		'customer_name' => 'str|required',
		'customer_contactor' => 'str|required',
		'customer_contact' => 'str|required',
		'customer_address' => 'str|required',
		'customer_location' => 'str|required',
		'customer_location_source' => 'int|required',
		'customer_city_id' => 'int|required',
		'customer_city_name' => 'str|required',
		'customer_region_id' => 'int|required',
		'customer_region_name' => 'str|required',
		'executor' => 'str|required',
        'executor_contact' => 'str|required',
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
				'order_amount' => 'int|required',
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
	 * @param array $data
	 * @return array
	 */
	public function format($data) {
		return $data;
	}
}
