<?php
/**
 * @name Order_Base_Action
 * @desc Order_Base_Action
 * @author lvbochao@iwaimai.baidu.com
 */

abstract class Order_Base_Action extends Nscm_Base_Action {

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
    public function init()
    {
        parent::init();
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
     */
    public function beforeMyExecute()
    {
        parent::beforeMyExecute();
        if ($this->intMethod == Order_Define_Const::METHOD_GET) {
            $arrInput = $this->arrReqGet;
        } else if ($this->intMethod == Order_Define_Const::METHOD_POST) {
            $arrInput = $this->arrReqPost;
        } else {
            trigger_error('must rewrite intMethod in class Action', E_ERROR );
            exit(-1);
        }
        if (!isset($this->arrInputParams) || !is_array($this->arrInputParams)) {
            trigger_error('must rewrite arrInputParams in class Action', E_ERROR );
            exit(-1);
        }
        $arrValidateResult = $this->validate($this->arrInputParams, $arrInput);
        if (is_array($this->arrFilterResult)) {
            $this->arrFilterResult = array_merge($arrValidateResult, $this->arrFilterResult);
        } else{
            $this->arrFilterResult = $this->validate($this->arrInputParams, $arrInput);
        }
        if ($this->boolCheckLogin) {
            $this->arrSession = Nscm_Lib_Singleton::get('Nscm_Lib_Map')->get('user_info');
        }
    }

    /**
     * real execute
     * @return array
     */
    public function myExecute()
    {
        $arrResult = $this->objPage->execute($this->arrFilterResult, $this->arrSession);
        return $arrResult;
    }

}