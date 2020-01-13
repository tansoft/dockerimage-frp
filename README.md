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

运行 start-server.sh
```

### 其中可选参数如下

* FRP_TOKEN=abcdef          访问服务器的密码
* FRP_SERVER_PORT=443       服务器公网端口
* FRP_SERVER_ADDR=1.2.3.4   服务器公网地址，默认0.0.0.0，因此服务器上无需设置
* FRP_ADMIN_NAME=           admin服务绑定的域名名称，通过token生成，一般无需修改
* FRP_ADMIN_PORT=7400       admin服务监听端口（绑定在127网卡上），一般无需修改
* FRP_ADMIN_TOKEN=fedcba    admin服务器的访问密码，通过token生成，一般无需修改

## 客户端安装-frpc方式

``` bash
git clone https://github.com/tansoft/dockerimage-frp.git

brew install frpc
```

## 客户端安装-docker方式

``` bash
git clone https://github.com/tansoft/dockerimage-frp.git

docker build -t frpmap -f Dockerfile.client . --build-arg FRP_TOKEN=<password> --build-arg FRP_SERVER_ADDR=<12.3.4.4>
```

## 客户端添加映射

``` bash
php dependents/addmap.php hostname port localport
```

http://host.docker.internal
