<?php
/**
 * @name Dao_Data
 * @desc 策略榜单
 * @auth wanggang01@iwaimai.baidu.com
 */
class Dao_Rpc
{
    public function getData($data)
    {
        $api = Bd_Conf::getAppConf('api');
        // 服务降级（ODP_SHOP集群的服务降级）
        $relegation = Bd_Conf::getConf('relegation/service');
        $req = [];
        if (empty($data)) {
            return [];
        }
        foreach ($data as $key => $value) {
            if (isset($relegation[$key]) && $relegation[$key] == 0) {
                Bd_Log::trace('relegation '.$key. 'service not request');
                continue;
            }
            $service = $api[$key];
            if (empty($service)) {
                throw new Order_Error(
                    Order_Error_Code::RAL_ERROR,
                    '',
                    "$key ral conf is not exists."
                );
            }
            $header = [];
            $extra  = [];

            if (isset($value['header'])) {
                $header = $value['header'];
                unset($value['header']);
            }
            if (isset($service['pathinfo'])) {
                $header['pathinfo'] = $service['pathinfo'];
            }
            if (isset($service['extra']) && is_array($service['extra'])) {
                $extra = $service['extra'];
            }
            if ($service['method'] == 'get') {
                $querystring = http_build_query($value);
                if (!empty($querystring)) {
                    $header['querystring'] = $querystring;
                }
                $input = [];
            } else {
                $input = $value;
            }

            $req[] = [
                $service['service'],
                $service['method'],
                $input,
                $extra,
                $header
            ];
        }
        try {
            $ralObj =  new Order_Util_Ral();
            $apiRes = $ralObj->ralMulti($req);
        } catch (Exception $e) {
            return [];
        }
        $serviceName = array_keys($data);
        $res = [];
        foreach ($serviceName as $key => $value) {
            if ($apiRes[$key] === false) {
                Bd_Log::warning($value. ' failed ral not success');
            } elseif (!is_array($apiRes[$key])) {
                $apiRes[$key] = @json_decode($apiRes[$key], true);
            }
            if (method_exists($this, $value)) {
                $apiRes[$key] = $this->$value($apiRes[$key]);
            } else {
                $apiRes[$key] = $this->defaultFormat($apiRes[$key], $value);
            }
            $res[$value] =  $apiRes[$key];
            Order_Debug::breakPoint($value, [
                'req' => $data[$value],
                'res' => $res[$value],
            ]);
        }
        return $res;
    }

    public function defaultFormat($data, $name)
    {
        if (empty($data) || (isset($data['error_no']) && $data['error_no'] != 0)) {
            Bd_Log::warning(sprintf('error[%s] msg[%s]', $data['error_no'], 'request '. $name. ' service exception'));
            return [];
        } else {
            return $data['result'];
        }
    }
}
