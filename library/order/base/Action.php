<?php
/**
 * @name Order_Base_Action
 * @desc Order_Base_Action
 * @author lvbochao@iwaimai.baidu.com
 */
abstract class Order_Base_Action extends Nscm_Base_Action {

    abstract function myConstruct();

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
        foreach ($this->arrInputParams as $key => $value) {
            $this->objValidator->addValidator($key, $arrInput[$key], $value);
        }
    }
}