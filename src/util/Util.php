<?php
namespace Kuyuan\WqCore\util;
use Model\W7McMembersModel;
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
     * @param array|string|int $_data 数据
     * @param array|string $_code 返回code
     * @param array|string $_message 返回信息
     */
    public static function returns($_data,$_code = 200,$_message = '')
    {
        $_data = array();
        $_args = func_get_args();
        foreach($_args as $_value) {
            if(is_int($_value)) {
                $_data['code'] = $_value;
            } else if(is_string($_value)) {
                $_data['message'] = $_value;
            } else if(is_array($_value)) {
                $_data['data'] = $_value;
            }
        }
        if($_data['code'] != 200) http_response_code($_data['code']);
        echo json_encode($_data);
        exit;
    }

    /**
     * 指定api请求为post
     */
    public static function appointPost()
    {
        global $_W;
        if(!$_W['ispost']) self::returns(302,'路由错误');
    }

    /**
     * 指定api请求为get
     */
    public static function appointGet()
    {
        global $_W;
        if($_W['ispost']) self::returns(302,'路由错误');
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

    /**
     * 完善系统用户头像昵称
     */
    public static function perfectNicknameAvatar()
    {
        global $_W;
        $_member = W7McMembersModel::where("uniacid",$_W["account"]["uniacid"])
            ->where("uid",$_W["member"]["uid"])
            ->field(array("nickname","avatar"))
            ->find();
        if(!$_member["nickname"] || !$_member["avatar"]) {
            $WxApi = \WeAccount::create();
            $_wx_member = $WxApi->fansQueryInfo($_W['openid']);
            W7McMembersModel::where("uniacid",$_W["account"]["uniacid"])
                ->where("uid",$_W["member"]["uid"])
                ->update(array("nickname"=>$_wx_member['nickname'],"avatar"=>$_wx_member['headimgurl']));
        }
    }


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