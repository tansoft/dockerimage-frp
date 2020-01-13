# 跳板机快速端口共享
只通过跳板机上的一个端口，快速实现把服务端内网服务暴露到本机

```
本机 --> 跳板机     内网服务
frpc     frps        ^
          ^          |
          |          |
     跳板机本机frpc -> +
```

* 跳板机上的frps只监听一个端口，同时这个端口也是vhost_http的端口，vhost_http直接是frp的admin服务，host和账号密码都通过token计算得出
* 客户端通过对admin的管理，实现按需增减映射，映射通过xtcp点对点穿透实现，因此不用多开端口

## 跳板机安装
跳板机需要安装好docker环境

``` bash
git clone https://github.com/tansoft/dockerimage-frp.git

docker build -t frpmap -f Dockerfile.server . --build-arg FRP_TOKEN=<password>
#其中还可以指定以下参数：
#--build-arg FRP_SERVER_PORT=443 --build-arg FRP_SERVER_ADDR=0.0.0.0

运行 start-server.sh
#如果FRP_SERVER_PORT修改了，请一并修改start-server.sh
```

## 客户端安装-docker方式

``` bash
git clone https://github.com/tansoft/dockerimage-frp.git

docker build -t frpmapcli -f Dockerfile.client . --build-arg FRP_TOKEN=<password> --build-arg FRP_SERVER_ADDR=<12.3.4.4>
#其中还可以指定以下参数：
#--build-arg FRP_ADMIN_PORT=7500 --build-arg FRP_SERVER_PORT=443
#7500 是本机上的管理端口

运行 start-client.sh

创建文件：config.php，配置token和服务器ip端口文件内容如下：
<?php
define('FRP_TOKEN','xxxx');
define('FRP_SERVER_ADDR','x.x.x.x');
define('FRP_SERVER_PORT','443');
```

## 客户端管理程序

``` bash
#在config.php 里修改需要的端口映射，然后运行
#$mapping = [['hostname',hostport,localport],['hostname',hostport,localport]]
php frpctrl.php

# hostname port localport
```

http://host.docker.internal
