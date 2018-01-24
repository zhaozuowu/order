namespace php order
namespace java me.ele.order
#创建业态订单返回异常
exception OrderUserException {
    1: string cl,
    2: string msg,
    3: map<string, string> fields,
    4: string type
}
#返回值
struct Data {
    1:required bool result
}
#取消状态返值
struct CancelData {
    1:required i32 isCancelled
}
#TMS完成门店签收
struct FinishOrderInfo {
    1:required string stockout_order_id,
    2:required i32 signup_status,
    3:required list<map<string,string>> signup_skus
}

#服务定义
service StockoutService {
    Data deliveryOrder(1:string stockout_order_id)
        throws (1: OrderUserException stockoutException),
    Data finishOrder(1:FinishOrderInfo objFinishOrderInfo )
        throws (1: OrderUserException stockoutException),
    CancelData getCancelStatus(1:string stockout_order_id)
        throws (1: OrderUserException stockoutException)
}
