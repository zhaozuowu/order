namespace php StockoutThrift
namespace java me.ele.StockoutThrift
#创建业态订单返回异常
exception StockoutThriftUserException {
    1: string cl,
    2: string msg,
    3: map<string, string> fields,
    4: string type
}
#返回值
struct Data {
    1:required  bool result
}
#TMS完成门店签收
struct finishOrderInfo {
    1:required i32 stockout_order_id,
    2:required i32 signup_status,
    3:required map<string,string> signup_upcs
}

#服务定义
service StockoutThriftService {
    Data deliveryOrder(1:i32 stockout_order_id)
        throws (1: StockoutThriftUserException stockoutException),
    Data finishOrder(1:finishOrderInfo objFinishOrderInfo )
            throws (1: StockoutThriftUserException stockoutException)
}
