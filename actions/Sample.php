<?php
/**
 * @name Action_Sample
 * @desc Action_Sample
 * @author lvbochao@iwaimai.baidu.com
 */

class Action_Sample extends Order_Base_Action
{
    /**
     * input params
     * @var array
     */
    protected $arrInputParams = [
        'param1' => 'int|required',
        'param2' => 'int|required',
    ];

    /**
     * method
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_POST;

    /**
     * page service
     * @var Service_Page_Sample
     */
    private $objPage;

    /**
     * init object
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Sample();
    }

    /**
     * real execute
     * @return array
     */
    public function myExecute()
    {
        return $this->objPage->execute($this->arrFilterResult);
    }

    /**
     * format result
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        return $data;
    }
}