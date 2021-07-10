<?php
namespace Kuyuan\WqCore\util;
class Util
{
    /**
     * 执行类
     * @param string $_className 类名字
     * @return mixed
     */
    public static function execClass($_className)
    {
        return new $_className();
    }

    /**
     * 执行类中的方法
     * @param object $_class 类
     * @param string $_methodName 类中的方法名字
     * @return mixed
     */
    public static function execMethod($_class,$_methodName)
    {
        return $_class->$_methodName();
    }

    /**
     * 返回json数据
     * @param array $_data 数据
     */
    public static function returns($_data)
    {
        if (is_array($_data)) {
            echo json_encode($_data);
        } else {
            echo $_data;
        }
        exit;
    }

    /**
     * 指定api请求为post
     */
    public static function appointPost()
    {
        global $_W;
        if(!$_W['ispost']) self::returns(array("code"=>302,"msg"=>"路由错误"));
    }

    /**
     * 指定api请求为get
     */
    public static function appointGet()
    {
        global $_W;
        if($_W['ispost']) returns(array("code"=>302,"msg"=>"路由错误"));
    }

    /**
     * 获取客户端用户uid
     */
    public static function uid()
    {
        global $_W;
        return KUYUAN_DEBUG ? 1 : $_W["member"]["uid"];
    }

    /**
     * 随机一个八位字符串，系统唯一
     */
    public static function random()
    {
        return sprintf('%x',crc32(microtime()));
    }
}