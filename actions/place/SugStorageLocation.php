<?php
/**
 * @name Action_SugStorageLocation
 * @desc Action_SugStorageLocation
 * @author huabang.xue@ele.me
 */
class Action_SugStorageLocation extends Order_Base_Action
{
    /**
     * 参数数组
     * @var array
     */
    protected $arrInputParams = [
        'warehouse_id' => 'int|required',
        'location_code' => 'regex|patern[/\w+/]',
        'is_default_store'   => 'int|default[2]|min[1]|max[2]',
    ];

    /**
     * method post
     * @var int
     */
    protected $intMethod = Order_Define_Const::METHOD_GET;

    protected $boolCheckAuth = false;

    /**
     * init object
     * @return mixed|void
     */
    public function myConstruct()
    {
        $this->objPage = new Service_Page_Place_SugStorageLocation();
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