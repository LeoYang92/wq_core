<?php
defined('IN_IA') or exit('Access Denied');

/**
 * 执行类方法
 */
if(!function_exists("execClass")){
    function execClass($_className)
    {
        return new $_className();
    }
}

/**
 * 执行类下的方法
 */
if(!function_exists("execMethod")){
    function execMethod($_class,$_methodName)
    {
        return $_class->$_methodName();
    }
}

/**
 * 接口返回数据
 */
if(!function_exists("returns")){
    function returns($_data)
    {
        if (is_array($_data)) {
            echo json_encode($_data);
        } else {
            echo $_data;
        }
        exit;
    }
}

/**
 * 路由请求方式指定为post
 */
if(!function_exists("appoint_post")){
    function appoint_post()
    {
        global $_W;
        if(!$_W['ispost']) returns(array("code"=>302,"msg"=>"路由错误"));
    }
}

/**
 * 路由请求方式指定为get
 */
if(!function_exists("appoint_get")){
    function appoint_get()
    {
        global $_W;
        if($_W['ispost']) returns(array("code"=>302,"msg"=>"路由错误"));
    }
}

//-----S动态生成文件或者目录-------------
if(!function_exists("kmedia_path")){
    /**
     * 随机生成文件路径
     * @param string $_dir 目录名字
     * @return string
     */
    function kmedia_path($_dir,$_ext)
    {
        global $_W;
        $_date = date("Y/m");
        $_dir_dir = $_dir."/".$_W["account"]["uniacid"]."/".$_date;
        if(!is_dir(ATTACHMENT_ROOT.$_dir_dir)){
            load()->func('file');
            mkdirs(ATTACHMENT_ROOT.$_dir_dir);
        }
        return $_dir_dir."/".md5(TIMESTAMP.uniqid(microtime(true))).".".$_ext;
    }
}

if(!function_exists("kstatic_dir")) {
    /**
     * 生成一个模块专属的静态目录
     * @param string $_dir 目录名称
     * @param string $_global 是否global全局目录
     * @return string
     */
    function kstatic_dir($_dir,$_global = false)
    {
        global $_W;
        $_uniacid_dir = $_global ? 'global' : $_W["account"]["uniacid"];
        $_dir = ATTACHMENT_ROOT.KUYUAN_NAME."/".$_uniacid_dir."/".$_dir."/";
        if(!is_dir($_dir)){
            load()->func('file');
            mkdirs($_dir);
        }
        return $_dir;
    }
}
//-----E动态生成文件或者目录-------------


//---------S其他----------------
if(!function_exists("ku_random")) {
    /**
     * 随机一个八位字符串，系统唯一
     * @return string
     */
    function ku_random()
    {
        return sprintf('%x',crc32(microtime()));
    }
}

if(!function_exists("ku_uid")) {
    /**
     * 获取当前用户uid
     * @return string
     */
    function ku_uid()
    {
        global $_W;
        return KUYUAN_DEBUG ? 1 : $_W["member"]["uid"];
    }
}
