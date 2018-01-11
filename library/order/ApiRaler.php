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
                'unfreezeskustock',
                json_encode($this->arrCurrentReq),
                $data['error_no'],
                $data['error_msg']
            ));
            return $data;
        }
        return $data;
    }
}
