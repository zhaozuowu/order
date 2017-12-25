<?php
/**
 * @name Action_DeliveryOrder
 * @desc TMS完成揽收
 * @author  zhaozuowu@iwaimai.baidu.com
 */

class Action_DeliveryOrder extends Ap_Action_Abstract
{


    /**
     * 请求信息
     * @var array
     */
    protected $arrReq;
    /**
     * responseData 响应信息
     * @var array
     */
    protected $responseData = [
        'error_no' => 0,
        'error_message' => 'success',
    ];

    /**
     * Service_Page_DeliveryOrder obj
     * @var  Service_Page_DeliveryOrder
     */
    private $objDeliveryOrder;

    /**
     * init
     */
    public function init()
    {

        $arrRequest = Saf_SmartMain::getCgi();
        $this->arrReq = $arrRequest['request_param'];
        $this->objDeliveryOrder = new Service_Page_DeliveryOrder();
    }


    /**
     * 执行入口
     */
    public function execute()
    {
        try {
            $this->init();
            $arrInput = $this->arrReq;
            $userPageInfo = $this->objDeliveryOrder->execute($arrInput);
            $this->responseData['result'] = $userPageInfo;
        } catch (Exception $e) {
            Bd_Log::warning($this->getErrorStr($e));
            $this->responseData['error_no'] = $e->getCode();
            $this->responseData['error_message'] = $e->getMessage();

        }
        return $this->response();
    }


    /**
     * 默认json输出
     *
     * @param string $display 输出方式
     */
    protected function response($display = 'json')
    {
        header('Content-type:application/json; charset=UTF-8');
        $result = $this->responseData;
        if (empty($result['result'])) {
            $result['result'] = (object)null;
        }
        echo json_encode($result);
    }

    /**
     * 获取异常错误信息
     * @param Exception $e
     * @return string
     */
    private function getErrorStr(Exception $e)
    {
        return $e->getFile() . "(" . $e->getLine() . ") error(" . $e->getCode() . ') ' . $e->getMessage() . " \n " . $e->getTraceAsString();
    }
}