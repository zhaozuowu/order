fis.match('*', {
    deploy: fis.plugin('http-push', {
        receiver: 'http://10.19.161.130:8148/receiver.php',
        to: '/home/map/odp/app/order' // 注意这个是指的是测试机器的路径，而非本地机器
    })
})