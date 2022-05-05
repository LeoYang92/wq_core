<?php
namespace Kuyuan\WqCore\controller;
use Endroid\QrCode\QrCode;

defined('IN_IA') or exit('Access Denied');
class Controller
{
    protected $_w = array();
    protected $_uniacid = 0; //公众号id
    protected $_uid = 0; // 当前用户id
    protected $Model = null;

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


    //S-----------简单数据操作-------------------
    /**
     * 通用简单的查询单条数据
     * @param array $_where
     * @param array $_fields
     * @param string $_attribute
     * @return mixed
     */
    public function easy_find(Array $_where,Array $_fields = array(),$_attribute = '')
    {
        $Result =  $this->Model
                ->where($_where)
                ->getAttribute($_attribute);
        if(count($_fields) > 0) {
            $Result = $Result->field($_fields);
        }
        return $Result->find();
    }

    /**
     * 简单的获取一个字段值
     * @param array $_where
     * @param string $_field
     * @return string
     */
    public function easy_value(Array $_where,$_field)
    {
        return $this->Model->where($_where)->getAttribute()->value($_field);
    }

    /**
     * 简单的获取一个字段总和
     * @param array $_where
     * @param $_field
     * @return integer
     */
    public function easy_sum($_field,Array $_where = array())
    {
        $Count = $this->Model->cache(false);
        if(count($_where) > 0) {
            $Count = $Count->where($_where);
        }
        return $Count->sum($_field);
    }

    /**
     * 简单的获取一个数据总数
     * @param array $_where
     * @return integer
     */
    public function easy_count(Array $_where = array())
    {
        $Count = $this->Model->cache(false);
        if(count($_where) > 0) {
            $Count = $Count->where($_where);
        }
        return $Count->count();
    }

    /**
     * 通用简单的查询多条语句
     * @param array $_where
     * @param array $_fields
     * @param array $_limit
     * @param array $_order
     * @param string $_attribute
     * @return array
     */
    public function easy_select(Array $_where,Array $_fields = array(),Array $_limit = array(),Array $_order = array(),$_attribute = '')
    {
        $Result = $this->Model
                ->where($_where)
                ->getAttribute($_attribute);
        if(count($_fields) > 0) {
            $Result = $Result->field($_fields);
        }
        if(count($_limit) > 1) {
            $Result = $Result->limit($_limit[0],$_limit[1]);
        }
        if(count($_order) > 1) {
            $Result = $Result->order($_order[0],$_order[1]);
        }
        return $Result->select();
    }

    /**
     * 简单的创建数据
     * @param array $_data 数据
     * @param boolean $_return_id 是否返回id
     * @return boolean|int
     */
    public function easy_create(Array $_data,$_return_id = false)
    {
        if(!$_return_id) {
            $_result = $this->Model->create($_data);
        } else {
            $_result = $this->Model->createId($_data);
        }
        return $_result;
    }

    /**
     * 简单修改数据
     * @param array $_where
     * @param array $_data
     * @return boolean
     */
    public function easy_update(Array $_where,Array $_data)
    {
        return $this->Model->where($_where)->update($_data);
    }

    /**
     * 简单的数据删除
     * @param mixed $_data 删除条件 或者 删除id
     * @param boolean $_true_delete 是否真删除
     * @return boolean
     */
    public function easy_delete($_data,$_true_delete = false)
    {
        return $this->Model->delete($_data,$_true_delete);
    }

    //E-----------简单数据操作-------------------

}