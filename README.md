# 介绍

一个简单的Res模型，包含code、msg、data、time_taked属性，可用于HTTP接口的返回值。

## 使用方法

### 安装

```sh
composer require wandoubaba/res ^2.0.0
```

使用

```php
use Wandoubaba/Res;

$res = new Res();
$res->success()
    ->setData('Hello world.');
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

| 属性       | 类型   | 举例                    | 说明                                                |
| ---------- | ------ | ----------------------- | --------------------------------------------------- |
| code       | int    | 200                     | 状态码，默认200表示成功                             |
| msg        | string | "操作成功"              | 状态描述                                            |
| data       | mix    | "Hello world."          | 返回数据，可以是任意类型                            |
| time_taked | double | 0.000014066696166992188 | 从后端res对象创建到返回结果期间消耗的时间，单位是秒 |

### 内置code和msg

```php
const ERROR = 0;                // System Exception
const SUCCESS = 200;            // success
const FAILED = -1;              // 通用一般错误
const NOT_LOGED = 306;          // 自定义306表示未登录
const HEARTBEAT = 1;            // 自定义1表示心跳消息，可忽略
const NOT_FOUND = 404;          // 404错误
const INTERNAL_ERROR = 500;     // 500错误
const NOT_ALLOWED = 308;        // 自定义308表示没有权限
const NO_DATA = 407;            // 自定义407表示数据不存在
const LOGIN_FAILED = 301;       // 自定义301表示登录失败
const NO_CHANGE = 408;          // 自定义408表示无数据变化

const CODE_MESSAGES = array(
    self::ERROR => 'System Exception',
    self::SUCCESS => 'Success',
    self::FAILED => 'Failed',
    self::NOT_LOGED => 'Not Logged In',
    self::HEARTBEAT => 'Heartbeat',
    self::NOT_ALLOWED => 'Permission Denied',
    self::NOT_FOUND => 'Not Found',
    self::INTERNAL_ERROR => 'System Error',
    self::NO_DATA => 'No Data',
    self::LOGIN_FAILED => 'Login Failed',
    self::NO_CHANGE => 'No Change',
);
```

### 通过依赖注入实现自定义错误码

> 以 `webman`框架为例。

```php
/* config/container.php */
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(config('dependence', []));
$builder->useAutowiring(true);
$builder->useAttributes(true);
return $builder->build();

/* config/dependence.php */
return [
    'code_messages' => app\biz\ResCode::CODE_MESSAGES,
];

/* app/utils/ResCode.php */
class ResCode
{
    const SUCCESS = 0;
    const FAILED = -1;
    const NOT_LOGED = 306;
    const TOKEN_ERROR = 309;

    const CODE_MESSAGES = [
        self::SUCCESS => '操作成功',
        self::FAILED => '操作失败',
        self::NOT_LOGED => '请先登录',
        self::TOKEN_ERROR => '令牌无效',

    ];
}

/* controller */
use Wandoubaba/Res;
use app\utils\ResCode;
use support\Container;

class TestController
{
    public function index(Request $request)
    {
        $res = Container::make(Res::class);
        $res->setCode(ResCode::TOKEN_ERROR);
        return json($res);
    }
}
```

### 参考

关于依赖注入的详细资料请参考:

[https://php-di.org/doc/getting-started.html](https://php-di.org/doc/getting-started.html)
[https://www.workerman.net/doc/webman/di.html](https://www.workerman.net/doc/webman/di.html)
