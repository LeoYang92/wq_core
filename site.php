<?php
defined('IN_IA') or exit('Access Denied');
include __DIR__."/vendor/autoload.php";
KUYUAN_DEBUG ? error_reporting(E_ALL & ~E_NOTICE) : error_reporting(0);

class kuyuan_nameModuleSite extends WeModuleSite {
    public function doWebApi()
    {
        if(KUYUAN_DEBUG){
            $this->ajax();
            $this->createRequestParamFile();
        }
        $this->api();
    }

    public function doMobileApi()
    {
        global $_W;
        if(KUYUAN_DEBUG)
        {
            $this->ajax();
            $this->createRequestParamFile();
        }
        if(!KUYUAN_DEBUG)
        {
            if($_W['container'] != 'wechat' || $_W['os'] != 'mobile'){
                returns(array("code"=>301,"msg"=>"仅支持在微信浏览器打开"));
            }
            checkauth();
        }
        $this->api();
    }

    private function api()
    {
        global $_GPC;
        if(isset($_GPC['pages']) && !empty($_GPC['pages'])){
            if(!is_file(MODULE_ROOT.'/api/'.ucwords($_GPC['pages']).'.php')){
                returns(array("code"=>404,"msg"=>ucwords($_GPC['pages']).'.php文件不存在'));
            }
            $_api = call_user_func('execClass','Api\\'.ucwords($_GPC['pages']));
            if(isset($_GPC['doing']) && !empty($_GPC['doing'])){
                $_methodName = $_GPC['doing'];
            }else{
                $_methodName = 'index';
            }
            if(!method_exists($_api,$_methodName)){
                returns(array("code"=>401,"msg"=>$_methodName.'()方法不存在'));
            }
            call_user_func('execMethod',$_api,$_methodName);
        } else {
            returns(array("code"=>500,"msg"=>"接口格式不正确"));
        }
    }


    /**
     * 外部ajaxq请求
     */
    private function ajax()
    {
        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Allow-Headers:Authorization');
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization");
    }

    /**
     * 创建请求参数文件，用户ab抗压测试
     */
    private function createRequestParamFile()
    {
        if(count($_POST) <= 0) return;
        $_content = '';
        foreach($_POST as $_k=>$_v){
            $_content .= $_k.'='.$_v.'&';
        }
        file_put_contents(MODULE_ROOT.'/post.text',$_content);
    }
}
