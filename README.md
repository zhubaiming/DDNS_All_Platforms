# 欢迎使用动态域名解析服务(DDNS) - 全平台平台

---

> 本脚本采用 PHP 语言进行开发
> 
> \* **本服务所包含的平台正在逐步完善中**

#### 已测试使用的系统

- [x] <font color="#008000">Linux</font>
- [x] <font color="#008000">MacOS</font>
- [ ] Windows

---

### 目前本服务所包含的运营商平台

- [x] <font color="#008000">Cloudflare</font>
- [x] <font color="#008000">阿里云</font>
- [ ] 腾讯云

---

### 使用前须知

---

### 使用前准备

> \* 运行本脚本前，请务必确保运行的系统内已安装 `net-tools` 工具
> 
> \* 运行本脚本前，请务必使用 [composer](https://getcomposer.org/) 安装扩展包( `composer install` )
> 
> \* 如需使用数据库进行IP地址记录，请自行安装数据库扩展并编写SQL语句，本脚本数据库连接方式采用PDO连接，请确保对应数据库扩展已安装
> 
> \* 如以 Cli 方式运行本脚本，请自行编写定时任务，以实现定时更新域名的解析

---

### 运行脚本

- **Cli 方式**

    ```shell
    php run.php --serverName=SERVERNAME --type=TYPE --rr=RR --domainName=DOMAINNAME [--remark=REMARK]
    ```
  
    参数说明：

    |参数|类型| 是否必填                       | 描述                              |示例|
    |:---|:---------------------------|:--------------------------------|:---|:---|
    |serverName|String| <font color="red">是</font> | 解析服务所使用的运营商平台                   |cloudflare|
    |type|String| <font color="red">是</font>                          | 解析记录类型：A - IPv4类型，AAAA - IPv6类型 |A|
    |rr|String| <font color="red">是</font>                          |解析主机记录，可同时解析多个，使用 \',\' 进行分割|@,www|
    |domainName|String| <font color="red">是</font>                          |域名名称|xxx.com|
    |remark|String| 否                          |解析记录备注，可同时填写多个，使用 \',\' 进行分割，顺序对应 `rr` 的顺序|备注1,备注2|

- **Docker 容器方式**