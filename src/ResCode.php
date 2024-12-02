<?php
namespace Wandoubaba;

/**
 * Predefined return codes and messages for components
 *
 * @author Aaron Chen <qiang.c@wukezhenzhu.com>
 */
class ResCode
{
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
}