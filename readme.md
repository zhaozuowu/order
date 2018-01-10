## ral services

1. create `./conf/ral/services/nscmvendor.conf`

``` shell
[CamelConfig]
[.ServiceConfig]


[..Local]
[...@Service]
Name  :  nscmvendor
DefaultPort : 8118
DefaultRetry : 1
DefaultConnectType  :  SHORT
DefaultConnectTimeOut : 300
DefaultReadTimeOut  :  2000
DefaultWriteTimeOut  :  500
[....@Server]

IP : 127.0.0.1
Port : 8080
[....Protocol]
Name : http
[....Converter]
Name : form
```



## wmq confs

1. add a command `cmd_xxx` in `app/order/library/define/Cmd.php`
2. create `app/order/page/commit/Cmdxxx.php` like `app/order/page/commit/Cmdnwmsorderstockoutcreate.php`
3. modify `home/map/wmq/pusher/conf/pusher/wmsg-group.yml` add command `cmd_xxx`
   ``` shell
        commands:                                                                                                             
         - send_msg
         - cmd_nwms_order_stockout_create
   ```
4. modify ips and url in `/home/map/wmq/pusher/conf/pusher/wmsg-group.yml`
   ``` shell
    service:
      type: dns
      tag: gzhxy
      ips:
        gzhxy:
        - 127.0.0.1:9993
      path: /commit/recv
      conntimeout: 4s
      readtimeout: 6s
      writetimeout: 2s    
   ```
5. use `Wm_Lib_Wmq_Commit::sendCmd` to send wmq command like code in `/home/map/service/page/business/CreateBusinessFormOrder.php`
6. create `conf/ral/services/wmqproxy.conf` 
    ``` shell
      [CamelConfig]
        [.ServiceConfig]


        [..Local]
        [...@Service]
        Name  :  wmqproxy
        DefaultPort : 
        DefaultRetry : 1
        DefaultConnectType  :  SHORT
        DefaultConnectTimeOut : 100
        DefaultReadTimeOut  :  1000
        DefaultWriteTimeOut  :  1000
        [....@Server]

        IP : 127.0.0.1
        Port : 9092
        [....Protocol]
        Name : http
        [....Converter]
        Name : form
    ```
6. restart wmq conf 

## api confs
.
