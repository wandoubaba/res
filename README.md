# 介绍

一个简单的Res模型，包含code、msg、data属性，可用于HTTP接口的返回值

## 使用方法

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