<?php
namespace Wandoubaba;

use Exception;
use ReflectionObject;
use ReflectionProperty;

use DI\Attribute\Inject;

class Res implements \JsonSerializable
{
    /**
     * Rewrite the jsonSerialize interface to implement json
     * serialization of code, msg, and data attributes
     *
     * @author Aaron <chenqiang@h024.cn>
     */
    public function jsonSerialize(): array
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

    /**
     * Receive the code and msg associative array flowing in in "DI" mode
     *
     * @var [type]
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     */
    #[Inject('code_messages')]
    private $customCodeMessages;

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

    /**
     * Quick return success, by default only affects code and msg, does not operate data
     *
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     *
     * @param  string $msg
     * @param  [type] $data
     *
     * @return void
     */
    public function success($msg = '', $data = null)
    {
        $this->setCode(ResCode::SUCCESS);
        if ($msg) {
            $this->setMsg($msg);
        }
        if (!is_null($data)) {
            $this->setData($data);
        }
        $this->end();
        return $this;
    }

    /**
     * Quick return fails. By default, only code and msg are affected, and data is not operated.
     *
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     *
     * @param  string $msg
     * @param  [type] $data
     *
     * @return void
     */
    public function failed($msg = '', $data = null)
    {
        $this->setCode(ResCode::FAILED);
        if ($msg) {
            $this->setMsg($msg);
        }
        if (!is_null($data)) {
            $this->setData($data);
        }
        $this->end();
        return $this;
    }

    /**
     * Determine whether the code of the returned object is "SUCCESS" code
     *
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        if ($this->code == ResCode::SUCCESS) {
            return true;
        }
        return false;
    }

    /**
     * Clear the code, msg and data attributes
     *
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     *
     * @return void
     */
    public function clear()
    {
        $this->code = null;
        $this->msg = null;
        $this->data = null;
        return $this;
    }

    /**
     * Set the data attribute
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

    /**
     * Set the code value. When there is a corresponding message
     * in the CODE_MESSAGES array, the msg value will be reset at
     * the same time.
     *
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     *
     * @param  [type] $code
     *
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
        $message = $this->returnCodeMessage($code);
        if ($message) {
            $this->msg = $message;
        }
        $this->end();
        return $this;
    }

    /**
     * Set the msg attribute
     *
     * @author Aaron Chen <qiang.c@wukezhenzhu.com>
     *
     * @param  [type] $message
     *
     * @return void
     */
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
        } catch (Exception $e) {
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
        $message = '';
        $codeMessages = $this->customCodeMessages;
        if (is_array($codeMessages)) {
            $codeMessages += ResCode::CODE_MESSAGES;
        } else {
            $codeMessages = ResCode::CODE_MESSAGES;
        }
        if (isset($codeMessages[$code])) {
            $message = $codeMessages[$code];
        }
        return $message;
    }
}