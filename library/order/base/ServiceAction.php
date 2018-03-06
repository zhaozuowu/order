<?php
/**
 * @name Order_Base_ServiceAction
 * @desc Order_Base_ServiceAction
 * @author lvbochao@iwaimai.baidu.com
 */
abstract class Order_Base_ServiceAction extends Nscm_Base_ServiceAction {
    /**
     * show price switch
     * @var bool $boolHidePrice
     */
    protected $boolHidePrice = true;

    /**
     * constructor
     * @return mixed
     */
    abstract function myConstruct();

    /**
     * @var Order_Base_Page
     */
    protected $objPage;

    /**
     * init function
     */
    public function init($objValidator = 'Wm_Validator')
    {
        parent::init($objValidator);
        $this->myConstruct();
    }

    /**
     * define it Order_Define_Const::METHOD_GET or Order_Define_Const::METHOD_POST
     * @var int $intMethod
     */
    protected $intMethod;

    /**
     * input params
     * key is input key, value is validate rule
     * @var array $arrInputParams
     */
    protected $arrInputParams;

    /**
     * session info
     * @var array
     */
    protected $arrSession;

    /**
     * add general validate
     */
    public function beforeValidate()
    {
        // do nothing, drop off NscmBaseAction beforeValidate
    }

    /**
     * validate
     * @param array $arrFormat
     * @param array $arrContent
     * @return array
     * @throws Wm_Error
     */
    public function validate($arrFormat, $arrContent)
    {
        $objValidator = new Wm_Validator();
        $arrArrayKeys = [];
        foreach ($arrFormat as $key => $value) {
            if (is_string($value)) {
                $objValidator->addValidator($key, $arrContent[$key], $value, $key . ' param invalid');
            } else {
                $objValidator->addValidator($key, $arrContent[$key], $value['validate'], $key . ' param invalid');
                $arrArrayKeys[] = $key;
            }
        }
        $arrRet = $objValidator->validate();
        foreach ($arrArrayKeys as $key) {
            if ('map' == $arrFormat[$key]['type']) {
                $arrAfterValidate = $this->validate($arrFormat[$key]['params'], $arrRet[$key]);
            } else {
                $arrAfterValidate = [];
                foreach ($arrRet[$key] as $row) {
                    $arrAfterValidate[] = $this->validate($arrFormat[$key]['params'], $row);
                }
            }
            $arrRet[$key] = $arrAfterValidate;
        }
        return $arrRet;
    }

    /**
     * add validate
     * @throws Wm_Error
     */
    public function beforeMyExecute()
    {
        parent::beforeMyExecute();
        $arrInput = $this->arrRequest;
        if (!isset($this->arrInputParams) || !is_array($this->arrInputParams)) {
            trigger_error('must rewrite arrInputParams in class Action', E_ERROR );
            exit(-1);
        }
        Bd_Log::debug('validator input: ' . json_encode($arrInput));
        $arrValidateResult = $this->validate($this->arrInputParams, $arrInput);
        if (is_array($this->arrFilterResult)) {
            $this->arrFilterResult = array_merge($arrValidateResult, $this->arrFilterResult);
        } else{
            $this->arrFilterResult = $this->validate($this->arrInputParams, $arrInput);
        }
        if ($this->boolCheckLogin) {
            $this->arrSession = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info');
            $this->arrFilterResult['_session'] = $this->arrSession;
        }
        Bd_Log::debug('validator output: ' . json_encode($this->arrFilterResult));
    }

    /**
     * real execute
     * @return array
     */
    public function myExecute()
    {
        $arrResult = $this->objPage->execute($this->arrFilterResult);
        return $arrResult;
    }

    /**
     * the price fields to hide
     * @var array
     */
    protected $arrPriceFields = [];

    /**
     * filter price in arrPriceFields
     * @param $row
     * @return array
     */
    protected function filterPrice($row) {
        if ($this->boolHidePrice) {
            $row = array_merge($row, array_fill_keys($this->arrPriceFields, Order_Define_Const::DEFAULT_EMPTY_RESULT_STR));
        }
        return $row;
    }
}