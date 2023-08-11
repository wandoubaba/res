<?php
namespace Wandoubaba\Res;

use ReflectionObject;
use ReflectionProperty;

class Res implements \JsonSerializable
{
    /**
     * 重写jsonSerialize接口，实现对属性序列化
     *
     * @author Aaron <chenqiang@h024.cn>
     */
    public function jsonSerialize()
    {
        $res = new \stdClass();
        $reflect = new ReflectionObject($this);
        // 序列化时只输出public和protected属性，不输出private和static属性
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED) as $prop) {
            $property = $prop->getName();
            if (!is_null($this->$property)) {
                $res->$property = $this->$property;
            }
        }
        return get_object_vars($res);
    }

    /** returncode的返回代码 */
    const ERROR = 0;                // 系统错误
    const SUCCESS = 200;            // 正确
    const FAILED = -1;              // 通用一般错误
    const NOT_LOGED = 306;          // 自定义306表示未登录
    const HEARTBEAT = 1;            // 自定义1表示心跳消息，可忽略
    const NOT_FOUND = 404;          // 404错误
    const INTERNAL_ERROR = 500;     // 500错误
    const NOT_ALLOWED = 308;        // 自定义308表示没有权限
    const NO_DATA = 407;            // 自定义407表示数据不存在
    const LOGIN_FAILED = 301;       // 自定义301表示登录失败
    const NO_CHANGE = 408;          // 自定义408表示无数据变化

    /**
     * 这里不能用protected，否则序列化时会被显示出来
     *
     * @var array
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     */
    private $code_message = array(
        self::ERROR => '内部错误',
        self::SUCCESS => '操作成功',
        self::FAILED => '操作失败',
        self::NOT_LOGED => '用户未登录',
        self::HEARTBEAT => '心跳',
        self::NOT_ALLOWED => '没有权限',
        self::NOT_FOUND => '请求路径不正确',
        self::INTERNAL_ERROR => '系统错误',
        self::NO_DATA => '数据不存在',
        self::LOGIN_FAILED => '登录失败',
        self::NO_CHANGE => '无数据变化',
    );

    protected $code = null;
    protected $msg = null;
    protected $data = null;
    protected $time_taked = null;
    private $start_stamp = null;
    private $end_stamp = null;

    /**
     * 构造函数
     *
     * @author Aaron <chenqiang@h024.cn>
     *
     * @param [type] $code
     * @param [type] $msg
     * @param [type] $data
     */
    public function __construct($code = null, $msg = null, $data = null)
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $data;
        $this->start();
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function success($data = null, $msg = '操作成功')
    {
        $this->msg = $msg;
        $this->code = self::SUCCESS;
        $this->data = is_null($data) ? $this->data : $data;
        $this->end();
        return $this;
    }

    public function failed($msg = '操作失败', $code = self::FAILED, $data = null)
    {
        $this->msg = $msg;
        $this->code = $code;
        $this->data = is_null($data) ? $this->data : $data;
        $this->end();
        return $this;
    }

    public function isSuccessful()
    {
        if ($this->code == self::SUCCESS) {
            return true;
        }
        return false;
    }

    public function clear()
    {
        $this->code = null;
        $this->msg = null;
        $this->data = null;
        return $this;
    }

    /**
     * 设置data属性
     *
     * @author Aaron <chenqiang@h024.cn>
     *
     * @param mixed $data
     * @param bool $convertId2String    默认true，是否将data中的id字段转为字符串（避免过长的id在js中失去精度）
     */
    public function setData($data, bool $convertId2String = true)
    {
        $this->data = $data;
        if ($convertId2String) {
            $this->convertDataId();
        }
        $this->end();
        return $this;
    }

    public function setCode($code)
    {
        $this->code = $code;
        $this->msg = $this->returnCodeMessage($this->code);
        $this->end();
        return $this;
    }

    public function setMsg($message)
    {
        $this->msg = $message;
        $this->end();
        return $this;
    }

    /**
     * 建议用start方法和end方法自动计算time_taked，这个方法备用
     *
     * @author Aaron <chenqiang@h024.cn>
     *
     * @param [type] $timeTaken
     */
    public function setTimeTaken($timeTaken)
    {
        $this->time_taked = $timeTaken;
        return $this;
    }

    /**
     * 在开始业务逻辑处理之前调用一下res->start方法记录开始时间
     *
     * @author Aaron <chenqiang@h024.cn>
     */
    public function start()
    {
        $this->start_stamp = microtime(true);
    }

    /**
     * 在业务逻辑结束后调用一下res->end方法自动记录结束时间并计算处理耗时（如果没有start_stamp，就不会自动计算）
     *
     * @author Aaron <chenqiang@h024.cn>
     */
    public function end()
    {
        $this->end_stamp = microtime(true);
        if ($this->start_stamp) {
            $this->time_taked = $this->end_stamp - $this->start_stamp;
        }
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function getTimeTaken()
    {
        return $this->time_taked;
    }

    /**
     * 把data中的id从int转为string
     *
     * @author Aaron <chenqiang@h024.cn>
     */
    public function convertDataId()
    {
        $this->data = $this->tryId2String($this->data);
        $this->end();
        return $this;
    }

    /**
     * 把另一个数组类型的res中的code, msg, data合并到当前对象
     *
     * @author Aaron <chenqiang@h024.cn>
     *
     * @param array|object $result
     */
    public function merge($result)
    {
        if (!is_array($result)) {
            $result = json_decode(json_encode($result), true);
        }
        if (isset($result['code'])) {
            $this->code = $result['code'];
        }
        if (isset($result['msg'])) {
            $this->msg = $result['msg'];
        }
        if (isset($result['data'])) {
            if (!is_array($this->data)) {
                $this->data = json_decode(json_encode($this->data), true);
            }
            if (is_array($this->data)) {
                $this->data = array_merge($this->data, $result['data']);
            } else {
                $this->data = $result['data'];
            }
        }
        $this->end();
        return $this;
    }

    /**
     * 将结果转为数组类型
     *
     * @author Aaron <chenqiang@h024.cn>
     */
    public function toArray()
    {
        $this->end();
        return json_decode(json_encode($this), true);
    }

    /**
     * 避免过大的int导致前端js处理时溢出，简单粗暴的把data中的int全部转为string
     *
     * @author Aaron <chenqiang@h024.cn>
     *
     * @param [type] $data
     */
    protected function tryId2String($data)
    {
        try {
            $data = json_decode(json_encode($data), true);
        } catch (\Exception $e) {
            // 什么也不用做
        }
        if (is_array($data)) {
            foreach (array_keys($data) as $key) {
                if (is_int($data[$key]) && ($key == 'id' || stripos($key, 'id'))) {
                    $data[$key] = strval($data[$key]);
                }
                if (is_array($data[$key])) {
                    $data[$key] = $this->tryId2String($data[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * 获得状态码对应的提示信息
     *
     * @author Aaron <chenqiang@h024.cn>
     *
     * @param [type] $code 状态码
     */
    protected function returnCodeMessage($code)
    {
        $message = '其他错误';
        if (isset($this->code_message[$code])) {
            $message = $this->code_message[$code];
        }
        return $message;
    }
}