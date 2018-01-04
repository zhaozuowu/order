<?php
/**
 * @name Order_Utility_Ral
 * @desc ral请求类
 * @author 陈传宝(chenchuanbao01@baidu.com)
 */
class Order_Util_Ral
{
    /**
     * 请求数组
     */
    protected $arrRequest = array();

    /**
     * timer
     */
    protected $timer;

    public function __construct()
    {
        $this->timer = new Bd_Timer();
    }

    /**
     * addHttpRequest
     *
     * 添加 http 请求
     *
     * @param mixed $key 数组 key
     * @param mixed $serverName 服务名
     * @param mixed $pathInfo 请求路径
     * @param mixed $query  查询请求
     * @param string $method 方法
     * @param string $host host
     * @param array $input input
     * @param array $extraHeaders extraHeaders
     * @return self
     */
    public function addHttpRequest(
        $key,
        $serverName,
        $pathInfo,
        $query,
        $method = 'get',
        $host = '',
        $input = array(),
        $extraHeaders = array()
    ) {
        $request = self::handleParams($serverName, $pathInfo, $query, $host, $method, $input, $extraHeaders);
        if (is_null($key)) {
            $this->arrRequest[] = $request;
        } else {
            $this->arrRequest[$key] = $request;
        }
        return $this;
    }

    /**
     * handleParams
     *
     * @param mixed $serverName serverName
     * @param mixed $pathInfo pathInfo
     * @param mixed $query query
     * @param mixed $host host
     * @param string $method method
     * @param array $input input
     * @param array $extraHeaders extraHeaders
     *
     * @return array
     */
    protected static function handleParams(
        $serverName,
        $pathInfo,
        $query,
        $host,
        $method = 'get',
        $input = array(),
        $extraHeaders = array()
    ) {
        $serverName = trim($serverName);
        $pathInfo   = trim($pathInfo);
        if (is_array($query)) {
            $query = http_build_query($query);
        }
        $host    = trim($host);
        $method  = trim($method);
        $input   = (array) $input;
        $headers = (array) $extraHeaders;
        $headers['pathinfo'] = $pathInfo;
        $headers['querystring'] = $query;
        !empty($host) && $headers['Host'] = $host;

        $result = array(
            $serverName,
            $method,
            $input,
            Bd_Env::getLogId(),
            $headers
        );
        return $result;
    }

    /**
     * nsheadRequest
     *
     * nshead 请求
     *
     * @param mixed $service 服务
     * @param mixed $input 输入
     * @param mixed $header 头部输出
     * @param mixed $extra 额外信息
     * @return mixed
     */
    public function nsheadRequest($service, $input, $method = 'post', $header = null, $extra = null)
    {
        Bd_Log::trace("start request of $service");
        $this->timer->reset();
        $this->timer->start();
        $ret = ral($service, $method, $input, $extra, $header);
        $this->timer->stop();
        if (RAL_RET_SUCCESS !== ($errNo = ral_get_errno())) {
            $errMsg = ral_get_error();
            $arrLog = array(
                'error_no:'  => $errNo,
                'error_msg'  => $errMsg,
                'serivce'   => $service,
                'header'    => Nscm_Lib_Util::jsonEncode($header),
                'inputdata' => Nscm_Lib_Util::jsonEncode($input),
                'timecost'  => $this->timer->getTotalTime(),
            );
            throw new Order_Error(
                Order_Error_Code::RAL_ERROR,
                '',
                $arrLog
            );
        }
        self::nsheadLog($this->timer->getTotalTime(), $service, $input, $header);
        return $ret;
    }

    /**
     * httpRequest
     *
     * @param mixed $serverName serverName
     * @param mixed $pathInfo pathInfo
     * @param mixed $query query
     * @param mixed $host host
     * @param string $method method
     * @param array $input input
     * @param array $extraHeaders extraHeaders
     *
     * @return mixed
     */
    public function httpRequest(
        $serverName,
        $pathInfo,
        $query,
        $host,
        $method = 'get',
        $input = array(),
        $extraHeaders = array()
    ) {
        list($service, $method, $input, $extra, $header) = self::handleParams(
            $serverName,
            $pathInfo,
            $query,
            $host,
            $method,
            $input,
            $extraHeaders
        );

        Bd_Log::trace("start request of $service");

        $this->timer->reset();
        $this->timer->start();
        $ret = ral($service, $method, $input, $extra, $header);
        $this->timer->stop();

        if (RAL_RET_SUCCESS !== ($errorNo = ral_get_errno())) {
            $errorMsg = ral_get_error();
            $arrLog = array(
                'error_no:' => $errorNo,
                'error_msg' => $errorMsg,
                'serivce'  => $service,
                'header'   => Nscm_Lib_Util::jsonEncode($header),
                'timecost' => $this->timer->getTotalTime(),
            );
            throw new Order_Error(
                Order_Error_Code::RAL_ERROR,
                '',
                $arrLog
            );
        }
        self::httpLog($this->timer->getTotalTime(), $service, self::getUrl($header));
        return $ret;
    }

    /**
     * multiRequest
     *
     * 并发请求
     *
     * @return mixed
     */
    public function multiRequest()
    {
        if (empty($this->arrRequest)) {
            throw new Order_Error(
                Order_Error_Code::PARAMS_ERROR,
                '',
                array('arrRequest' => $this->arrRequest)
            );
        }

        $this->timer->reset();
        $this->timer->start();
        $apiRes = ral_multi($this->arrRequest);
        $this->timer->stop();

        if (RAL_RET_SUCCESS !== ($errorNo = ral_get_errno())) {
            $erorMsg = ral_get_error();
            $arrLog = array(
                'error_no:' => $errorNo,
                'error_msg' => $erorMsg,
                'serivce'  => $service,
                //'header'   => Nscm_Lib_Util::jsonEncode($header),
                'timecost' => $this->timer->getTotalTime(),
            );
            throw new Order_Error(
                Order_Error_Code::RAL_ERROR,
                '',
                $arrLog
            );
        }
        self::multiRequestLog($this->timer->getTotalTime(), $this->arrRequest);
        return $apiRes;
    }

    /**
     * multiRequestLog
     *
     * @param mixed $timeCost
     * @param mixed $arrArgs
     *
     * @return void
     */
    protected static function multiRequestLog($timeCost, $arrArgs)
    {
        Bd_Log::debug("log:multi-request", $timeCost, Nscm_Lib_Util::jsonEncode($arrArgs));
    }

    /**
     * nsheadLog
     *
     * @param mixed $totalTime totalTime
     * @param mixed $service service
     * @param mixed $input input
     * @param array $arrArgs arrArgs
     *
     * @return void
     */
    protected static function nsheadLog($totalTime, $service, $input, $arrArgs = array())
    {
        $jsonInput = Nscm_Lib_Util::jsonEncode($input);
        $jsonArgs = Nscm_Lib_Util::jsonEncode($arrArgs);

        $arr_args = array(
            "ral_service" => $service,
            "ral_cost" => $totalTime,
            "input" => $jsonInput,
            "extra" => $jsonArgs
        );
        Bd_Log::trace("nsheadlog: $service", 0, $arr_args);
    }

    /**
     * httpLog
     *
     * @param mixed $totalTime totalTime
     * @param mixed $service service
     * @param mixed $url url
     *
     * @return void
     */
    protected static function httpLog($totalTime, $service, $url)
    {
        $arr_args = array(
            "ral_service" => $service,
            "ral_url" => $url,
            "ral_cost"=> $totalTime,
        );
        Bd_Log::trace("log:httpRequest $service", 0, $arr_args);
    }

    /**
     * getUrl
     *
     * @param array $header header
     *
     * @return string
     */
    protected function getUrl(array $header)
    {
        $url = '';
        if (isset($header['Host']) && !empty($header['Host'])) {
            $url .= 'http://' . $header['Host'];
        }
        if (isset($header['pathinfo']) && !empty($header['pathinfo'])) {
            $url .= $header['pathinfo'];
        }
        if (isset($header['querystring']) && !empty($header['querystring'])) {
            $url .= '?' . $header['querystring'];
        }
        return $url;
    }

    /**
     * [ralMulti 原生ral_multi请求方式]
     * @param  [type] $multiReq [description]
     * @return [type]           [description]
     */
    public function ralMulti($multiReq)
    {
        if (empty($multiReq)) {
            return array();
        }
        $this->timer->reset();
        $this->timer->start();
        $apiRes = ral_multi($multiReq);
        $this->timer->stop();

        //所有请求都有问题才会打印异常日志
        if (RAL_RET_SUCCESS !== ($errorNo = ral_get_errno())) {
            $erorMsg = ral_get_error();
            $arrLog = array(
                'error_no:' => $errorNo,
                'error_msg' => $erorMsg,
                'multi_req'   => Nscm_Lib_Util::jsonEncode($multiReq),
                'timecost' => $this->timer->getTotalTime(),
            );
            Bd_Log::warning(sprintf(
                'error[%s] method[%s] msg[%s]',
                Order_Error_Code::RAL_ERROR,
                '',
                json_encode($arrLog)
            ));
        }
        self::multiRequestLog($this->timer->getTotalTime(), $multiReq);
        return $apiRes;
    }
}
