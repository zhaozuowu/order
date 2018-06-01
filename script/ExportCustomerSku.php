<?php
/**
 * @name ExportCustomerSku.php
 * @desc ExportCustomerSku.php
 * @author yu.jin03@ele.me
 */
class ExportCustomerSku {

    protected $conn;

    /**
     * ExportCustomerSku constructor.
     */
    public function __construct()
    {
        $this->conn = Bd_Db_ConnMgr::getConn('nwms_order/nwms_order_gzns');
    }

    public function execute()
    {
        //get customer infos
        $strCustomerInfoSql = "select customer_city_id, customer_city_name, customer_region_name, customer_id, customer_name, 
                                customer_address, from_unixtime(create_time, '%Y-%m-%d') as create_time
                                from stockout_order group by customer_id";
        $arrCustomerInfo = $this->conn->query($strCustomerInfoSql);
        foreach ((array)$arrCustomerInfo as $arrCustomerInfoItem) {
            //get customer orders
            $intCustomerId = $arrCustomerInfoItem['customer_id'];
            if ('11111111' == $intCustomerId || '12222322666' == $intCustomerId
                || '1222233' == $intCustomerId || '122223445' == $intCustomerId) {
                continue;
            }
            $strOrderIdSql = "select stockout_order_id from stockout_order where customer_id = '$intCustomerId'";
            $arrOrderIds = $this->conn->query($strOrderIdSql);
            //get stockout order skus
            $arrOrderIds = array_column($arrOrderIds, 'stockout_order_id');
            $strOrderIds = implode(',', $arrOrderIds);
            $strOrderSkusSql = "select sku_id, sku_name, sku_effect_type, sku_effect_day 
                                from stockout_order_sku where stockout_order_id in ($strOrderIds)";

            $arrOrderSkus = $this->conn->query($strOrderSkusSql);
            //unique skus in customer order
            $arrMapSkuInfo = [];
            foreach ((array)$arrOrderSkus as $arrOrderSkusItem) {
                $intSkuId = $arrOrderSkusItem['sku_id'];
                if (empty($intSkuId)) {
                    continue;
                }
                $arrMapSkuInfo[$intSkuId] = $arrOrderSkusItem;
            }
            //write to file
            foreach ((array)$arrMapSkuInfo as $arrSkuItem) {
                $arrSkuItem = $arrCustomerInfoItem + $arrSkuItem;
                $arrSkuItem['sku_id'] = "'" . $arrSkuItem['sku_id'];
                $arrSkuItemVals = array_values($arrSkuItem);
                $strSkuItemVals = implode(',', $arrSkuItemVals);
                $strSkuItemVals = $strSkuItemVals . "\r\n";
                echo $strSkuItemVals;
                $objFp = fopen($arrSkuItem['customer_city_id'] . '_customerskus', 'a');
                fwrite($objFp, $strSkuItemVals);
                fclose($objFp);
            }
        }
        fclose($objFp);
    }
}

Bd_Init::init();
$obj = new ExportCustomerSku();
$obj->execute();
