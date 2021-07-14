<?php
namespace Kuyuan\WqCore\api;
use Kuyuan\WqCore\util\Util;

abstract class Api
{
    protected $_gpc = array();
    // 执行api调用的类集合，防止重复实例化controller中的类
    protected $_class = array();

    protected function __construct()
    {
        global $_GPC;
        $this->_gpc = $_GPC;
    }

    /**
     * 实例化控制器中的类，防止重复实例化
     * @param $_class_name
     * @return object
     */
    protected function newController($_class_name)
    {
        if(isset($this->_class[$_class_name])) {
            $Class = $this->_class[$_class_name];
        } else {
            $Class = $this->_class[$_class_name] = Util::execClass("Controller\\".$_class_name);
        }
        return $Class;
    }
}