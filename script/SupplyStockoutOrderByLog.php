<?php
/**
 * @name SupplyStockoutOrderByLog.php
 * @desc SupplyStockoutOrderByLog.php
 * @author yu.jin03@ele.me
 */
class SupplyStockoutOrderByLog
{
    /**
     * 执行回放wmq脚本
     */
    public function execute() {
        $objLogFp = fopen("cmd.log", 'r');
        while (!feof($objLogFp)) {
            $strLine = fgets($objLogFp);
            $strLine = trim($strLine);
            $this->sendCmd($strLine);
        }
    }

    /**
     * 通过日志回放wmq
     * @param $strLine
     */
    public function sendCmd($strLine)
    {
        $arrLine = explode(' ', $strLine);
        foreach ((array)$arrLine as $strItem) {
            $strSubStr = substr($strItem, 0, 4);
            if ('Data' != $strSubStr) {
                continue;
            }
            $intStart = strpos($strItem, '[');
            $intEnd = strpos($strItem, ']');
            $strParams = substr($strItem, $intStart+1, $intEnd-$intStart-1);
            $arrParams = json_decode($strParams, true);
            $ret = Order_Wmq_Commit::sendWmqCmd(Order_Define_Cmd::CMD_CREATE_STOCKOUT_ORDER, $arrParams,
                strval($arrParams['stockout_order_id']));
            echo sprintf("send cmd[stockout_order_create] ret[%s] stockout_order_id[%s]",
                        $ret, $arrParams['stockout_order_id']);
            sleep(0.1);
        }
    }
}
Bd_Init::init();
$objSupplyType = new SupplyStockoutOrderByLog();
$objSupplyType->execute();
