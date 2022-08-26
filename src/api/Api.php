<?php
namespace Kuyuan\WqCore\api;
use Kuyuan\WqCore\util\Auth;
use Kuyuan\WqCore\util\Util;

abstract class Api
{
    protected $_gpc = array();
    // 执行api调用的类集合，防止重复实例化controller中的类
    protected $_class = array();
    // 需要验证的登录token方法名,在子类初始化
    protected $_check_auth = array();

    protected function __construct()
    {
        global $_GPC;
        $this->_gpc = $_GPC;
        // 是否执行验证Auth
        if(in_array($this->_gpc['doing'],$this->_check_auth)) {
            $this->checkAuth();
        }
    }

    /**
     * 验证登录auth
     * @return void
     */
    private function checkAuth()
    {
        try {
            $_auth_token = Util::getHeader('Authorization');
            if(!$_auth_token) {
                throw new \Exception('UnAuthorization',401);
            }
            $_tokens = Auth::decodeToken($_auth_token);
            if(!$_tokens) {
                throw new \Exception('UnAuthorization',401);
            }
        } catch (\Exception $e) {
            Util::returns($e->getCode(),$e->getMessage());
        }
    }

    /**
     * 实例化控制器中的类，防止重复实例化
     * @param $_class_name
     * @return object
     */
    protected function newController($_class_name,$_namespace = "Controller\\")
    {
        if(isset($this->_class[$_class_name])) {
            $Class = $this->_class[$_class_name];
        } else {
            $Class = $this->_class[$_class_name] = Util::execClass($_namespace.$_class_name);
        }
        return $Class;
    }
}