# 介绍

一个简单的Res模型，包含code、msg、data属性，可用于HTTP接口的返回值

## 使用方法

新版本可以接收名为`code_messages`的依赖注入，注入的内容是键值数组，因此可以支持自定义错误码，例如:

```php
[
    309 => 'token error',
    502 => 'gateway error'
];
```

依赖注入参考:

<https://php-di.org/doc/getting-started.html>
<https://www.workerman.net/doc/webman/di.html>

安装

```sh
composer require wandoubaba/res
```

使用

```php
use Wandoubaba/Res;

$res = new Res();
$res->success();
$res->setData('Hello world.');
var_dump($res);
```

属性说明

```json
{
    "code": 200,
    "msg": "操作成功",
    "data": "Hello world.",
    "time_taked": 0.000014066696166992188
}
```

|属性|类型|举例|说明|
|---|---|---|---|
|code|int|200|状态码，默认200表示成功|
|msg|string|"操作成功"|状态描述|
|data|mix|"Hello world."|返回数据，可以是任意类型|
|time_taked|double|从后端res对象创建到返回结果期间消耗的时间，单位是秒|
