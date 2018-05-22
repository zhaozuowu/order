<?php
/**
 * @name Action_ConfirmStockinOrder
 * @desc 确认销退入库单
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_ConfirmStockinOrder extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'stockin_order_id' => 'regex|patern[/^((SIO)\d{13})?$/]',
        'stockin_order_remark' => 'strutf8',
        'ignore_check_date' => 'int|default[0]',
        'sku_info_list' => [
            'validate' => 'json|required|decode',
            'type' => 'array',
            'params' => [
                'sku_id' => 'int|min[1000000]|max[9999999]',
                'real_stockin_info' => [
                    'validate' => 'arr|decode',
                    'type' => 'array',
                    'params' => [
                        'amount' => 'int',
                        'sku_good_amount' => 'int',
                        'sku_defective_amount' => 'int',
                        'expire_date' => 'int',
                    ]
                ],
            ],
        ],
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * @return array|null
     * @throws Order_BusinessError
     */
    public function myExecute()
    {
        try {
            return parent::myExecute();
        } catch (Order_BusinessError $e) {
            if (Order_Error_Code::NOT_IGNORE_ILLEGAL_DATE == $e->getCode()) {
                $arrResult = $e->getArrArgs();
                return $arrResult;
            } else {
                throw $e;
            }
        }

    }

    /**
     * construct
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Stockin_ConfirmStockinOrder();
    }

    /**
     * format
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $boolSuccess = true;
        $arrWarningInfo = [];
        if (!empty($data)) {
            $boolSuccess = false;
            foreach ($data as $intSkuId => $arrDates) {
                $arrWarningInfo[] = [
                    'sku_id' => $intSkuId,
                    'warning_dates' => $arrDates,
                ];
            }
        }
        $result = [
            'success' => $boolSuccess,
            'warning_info' => $arrWarningInfo,
        ];
        return $result;
    }
}