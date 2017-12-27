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
     * array validate
     * @var array
     */
    private $arrArrayValidate;

    /**
     * add general validate
     */
    public function beforeValidate()
    {
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
        $this->arrArrayValidate = [];
        foreach ($this->arrInputParams as $key => $value) {
            if (is_string($value)) {
                $this->objValidator->addValidator($key, $arrInput[$key], $value);
            } else if (is_array($value)) {
                $this->objValidator->addValidator($key, $arrInput[$key], $value['validate']);
                $this->arrArrayValidate[$key] = $value;
            }
        }
    }

    /**
     * add validate
     */
    public function beforeMyExecute()
    {
        parent::beforeMyExecute();
        foreach ($this->arrArrayValidate as $key => $value) {
            $arrBefore = $this->arrFilterResult[$key];
            if (!is_array($arrBefore)) {
                trigger_error('array param must be decoded', E_ERROR );
            }
            if ('map' == $value['type']) {
                $objValidator = new Wm_Validator();
                foreach ($value['params'] as $strParamKey => $strValidate) {
                    $objValidator->addValidator($strParamKey, $arrBefore[$strParamKey], $strValidate);
                }
                $arrResult = $objValidator->validate();
            } else {
                $arrResult = [];
                foreach ($arrBefore as $row) {
                    $objValidator = new Wm_Validator();
                    foreach ($value['params'] as $strParamKey => $strValidate) {
                        $objValidator->addValidator($strParamKey, $row[$strParamKey], $strValidate);
                    }
                    $arrResultRow = $objValidator->validate();
                    $arrResult[] = $arrResultRow;
                }
            }
            $this->arrFilterResult[$key] = $arrResult;
        }
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

}