<?php
/**
 * Created by PhpStorm.
 * User: think
 * Date: 2018/1/9
 * Time: 11:14
 */

class Order_Util_HuskarFormat implements Nscm_Interface_DataFormat
{
    /**
     * 默认格式化方法
     * @param $data
     * @param $name
     * @return mixed
     */
    public function defaultFormat($data, $name)
    {
        if (empty($data) || ($data['error_no'] != 0)) {
            Bd_Log::warning(sprintf(
                '[%s] request service exception data[%s] error[%s] msg[%s]',
                $name,
                json_encode($data),
                $data['error_no'],
                $data['error_msg']
            ));
        }
        return $data;
    }
}