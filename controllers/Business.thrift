namespace php BusinessThrift
namespace java me.ele.BusinessThrift
namespace python BusinessThrift
#创建业态订单返回异常
exception OrderUserException {
    1: string cl,
    2: string msg,
    3: map<string, string> fields,
    4: string type
}
#返回商品信息
struct RetSkuInfo {
    1:required string sku_id,
    2:required i32 cost_price_tax,
    3:required i32 cost_price_untax,
    4:required bool is_empty
}
#返回值
struct Data {
    1:required string stockout_order_id,
    2:required list<RetSkuInfo> skus
}
#业态订单sku信息
struct BusinessFormOrderSku {
    1:required string sku_id,
    2:required string upc_id,
    3:required i32 order_amount,
    4:required i32 display_type,
    5:required i32 display_floor
} 
#业态订单信
struct BusinessFormOrderInfo {
    1:required i32 business_form_order_type,
    2:required i32 order_supply_type,
    3:required i32 business_form_order_price,
    4:required string business_form_order_remark,
    5:required string warehouse_id,
    6:required string customer_site_id,
    7:required string customer_site_name,
    8:required string customer_id,
    9:required string customer_contact,
    10:required string customer_contactor,
    11:required string customer_address,
    12:required string customer_location,
    13:required i32 customer_location_source,
    14:required string customer_city_name,
    15:required list<BusinessFormOrderSku> skus 
}
#服务定义
service BusinessThriftService {
    Data createBusinessFormOrder(1:required BusinessFormOrderInfo objBusinessFormOrderInfo)
        throws (1: OrderUserException userException)
}
