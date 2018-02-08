namespace php Business
namespace java me.ele.Business
#创建业态订单返回异常
exception OrderUserException {
    1: string cl, #错误分类
    2: string msg, #错误原因
    3: map<string, string> fields, #包含错误信息
    4: string type
}
#返回商品信息
struct RetSkuInfo {
    1:required string sku_id,
    2:required i32 cost_price_tax,
    3:required i32 cost_price_untax,
    4:required i32 order_amount,
    5:required i32 distribute_amount
}
#返回值
struct Data {
    1:required string stockout_order_id,
    2:required list<RetSkuInfo> skus
}
#业态订单sku信息
struct BusinessFormOrderSku {
    1:required string sku_id,
    3:required i32 order_amount
}
#货架信息
struct ShelfInfo {
    1:required i8 supply_type,
    2:required map<string, i32> devices 
}
#业态订单信
struct BusinessFormOrderInfo {
    1:required string logistics_order_id,
    2:required ShelfInfo shelf_info,
    3:required i32 business_form_order_type,
    4:required i32 order_supply_type,
    5:optional string business_form_order_remark,
    6:required string customer_id,
    7:required string customer_name,
    8:required string customer_contactor,
    9:required string customer_contact,
    10:required string customer_address,
    11:required string customer_location,
    12:required i8 customer_location_source,
    13:required i32 customer_city_id,
    14:required string customer_city_name,
    15:required i32 customer_region_id,
    16:required string customer_region_name,
    17:required list<BusinessFormOrderSku> skus 
}
#服务定义
service BusinessService {
    Data createBusinessFormOrder(1:required BusinessFormOrderInfo objBusinessFormOrderInfo)
        throws (1: OrderUserException userException)
}
