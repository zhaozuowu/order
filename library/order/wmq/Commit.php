<?php
/**
 * @name Order_Wmq_Commit
 * @desc send wmq cmd
 * @author jinyu02@iwaimai.baidu.com
 */
class Order_Wmq_Commit extends Wm_Lib_Wmq_Commit {
    
    /**
     * send wmq cmd
     * @param string $strCmd
     * @param array $arrParams
     * @param string $key
     * @param string $Topic
     * @return integer
     */
    public static function sendWmqCmd($strCmd, $arrParams, $strKey = '', $strTopic = '') {
        $arrWmqConfig = Order_Define_Cmd::DEFAULT_WMQ_CONFIG;
        if (!empty($strKey)) {
            $arrWmqConfig['Key'] = $strKey;
        }
        if (!empty($strTopic)) {
            $arrWmqConfig['Topic'] = $strTopic;
        }
        return self::sendCmd($strCmd, $arrParams, $arrWmqConfig);
    }
}
