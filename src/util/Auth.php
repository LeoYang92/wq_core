<?php
namespace Kuyuan\WqCore\util;
class Auth
{
    /**
     * 获取自定义请求头
     * @param $_attribute
     * @return mixed
     */
    public static function getHeader($_attribute)
    {
        $_attribute = strtoupper('http_'.$_attribute);
        return $_SERVER[$_attribute];
    }
}