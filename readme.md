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