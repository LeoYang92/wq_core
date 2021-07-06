<?php
namespace Kuyuan\WqCore\controller;
use Endroid\QrCode\QrCode;

defined('IN_IA') or exit('Access Denied');
class Controller
{
    protected $_w = array();
    protected $_uniacid = 0; //公众号id
    protected $_uid = 0; // 当前用户id

    protected function __construct()
    {
        global $_W;
        $this->_w = $_W;
        $this->_uniacid = $_W["account"]["uniacid"];
        $this->_uid = KUYUAN_DEBUG ? 1 : $this->_w["member"]["uid"];
    }

    /**
     * 获取当前用户uid
     */
    public function getUid()
    {
        return $this->_uid;
    }


    /**
     * 获取，生成一个二维码
     * @param string $_dir_name 二维码存放的目录名字
     * @param string $_file_name 文件名
     * @param string $_url  二维码储存的链接
     * @param boolean $_build 是否重新生成
     * @return mixed
     */
    protected function getQrcode($_dir_name,$_file_name,$_url,$_build = false)
    {
        $_dir = ATTACHMENT_ROOT.KUYUAN_NAME.'/'.$this->_uniacid.'/'.$_dir_name.'/';
        $_filename = $_dir.$_file_name.'.png';
        $_path = KUYUAN_NAME.'/'.$this->_uniacid.'/'.$_dir_name.'/'.$_file_name.'.png';
        if(is_file($_filename) && !$_build){
            return $this->_w['attachurl_local'].$_path;
        }else{
            if(!is_dir($_dir)){
                mkdir($_dir,0777,true);
            }
            $Qrcode = new QrCode($_url);
            $Qrcode->setSize(500);
            $Qrcode->writeFile($_filename);
            return $this->_w['attachurl_local'].$_path;
        }
    }

}