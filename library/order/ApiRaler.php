<?php
/**
 * @name Order_ApiRaler
 * @desc api raler
 * @auth wanggang01@iwaimai.baidu.com
 */

class Order_ApiRaler extends Nscm_lib_ApiRaler
{

    /**
     * [unfreezeskustock description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     *
     * @monitor
     */
    protected function unfreezeskustock($data)
    {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
        }
        return $data;
    }

    /**
     * stock adjuststockout接口格式化返回值
     * @param $data
     * @return mixed
     */
    protected function adjuststockout($data)
    {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
        }
        return $data;
    }


    /**
     * stock cancelfreezeskustock接口格式化返回值
     * @param $data
     * @return mixed
     */
    protected  function cancelfreezeskustock($data)
    {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
        }
        return $data;
    }
    /**
     * stockdetail接口格式化返回值
     * @param $data
     * @return mixed
     */
    protected function stockdetail($data)
    {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * @throws Order_BusinessError
     */
    protected function nscminbouddirect($data)
    {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception req_info[%s] error[%s] msg[%s]',
                __METHOD__,
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
            Order_BusinessError::throwException(Order_Error_Code::RAL_ERROR);
        }
        return $data;
    }

}
