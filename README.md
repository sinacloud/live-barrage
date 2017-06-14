新浪云直播弹幕服务器实例，弹幕服务器基于swoole，运行于新浪云自定义运行环境。

创建应用
--------
从<http://sae.sina.com.cn/?m=apps&a=create> 这里创建一个自定义运行环境应用，如图所示：

![image](https://github.com/sinacloud/live-barrage/raw/master/images/create_app_d.png)

选择【Ubuntu】系统。

SSH登录
---------
请参考教程 <http://www.sinacloud.com/home/index/faq_detail/doc_id/175.html>

初始化环境
-----------
需要安装PHP运行环境，redis扩展，swoole扩展，参考本项目中的 init-env.sh，也可以快速执行以下脚本。

```
apt-get update
apt-get install curl -y
curl 'http://sinacloud.net/opensource/swoole/init-env.sh'|bash
```

创建redis服务
--------------
该服务依赖redis服务，需要从新浪云管理中心创建一个redis服务，替换``function.php``中的连接信息和密码即可。

上传代码
--------
将server.php、function传到``/barrage``目录下即可。

运行代码
---------
```
cd /barrage/
php server.php
```

测试代码
---------
在chrome的控制台中直接运行
```
var s = new WebSocket('ws://YOURAPPNAME.applinzi.com');
s.send('hello sae');
```