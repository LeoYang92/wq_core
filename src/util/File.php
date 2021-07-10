<?php
namespace Kuyuan\WqCore\util;
class File
{
    /**
     * 生成一个文件存储路径
     * @param string $_dir 目录名字
     * @param string $_ext 后缀名字
     * @return string
     */
    public static function path($_dir,$_ext)
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

    /**
     * 生成一个模块专属的静态目录
     * @param $_dir
     * @param bool $_global
     * @return string]
     */
    public static function dir($_dir,$_global = false)
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