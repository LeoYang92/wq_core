<?php
namespace Kuyuan\WqCore\wx;

/**
 * 微信消息，模板消息，一次性订阅消息
 * Class PayConfig
 * @package Core\wx
 */
class Message
{
    /**
     * 微擎二次封装发送模板消息
     * @param $_openid
     * @param $_template_id
     * @param $_data
     * @param string $_url
     * @param string $_font_color
     * @param array $_min_app
     * @return mixed
     */
    public static function sendTpl($_openid,$_template_id,$_data,$_url = '',$_font_color = '',$_min_app = array())
    {
        $WeAccount = \WeAccount::create();
        return !is_error($WeAccount->sendTplNotice($_openid,$_template_id,$_data,$_url,$_font_color,$_min_app));
    }

    /**
     * 获取公众号模板消息所属行业
     */
    public static function getTplClass()
    {
        $WeAccount = \WeAccount::create();
        $_response = ihttp_get("https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token=".$WeAccount->getAccessToken());
        $_result = false;
        if(isset($_response["content"])) {
            $_content = json_decode($_response["content"],true);
            if(isset($_content["primary_industry"])) {
                $_result = $_content;
                unset($_content);
            }
        }
        return $_result;
    }

    /**
     * 获取模板消息id
     * @param string $_code 模板消息编号
     * @return mixed
     */
    public static function getTplId($_code)
    {
        $WeAccount = \WeAccount::create();
        $_response = ihttp_post(
            "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=".$WeAccount->getAccessToken(),
            json_encode(array("template_id_short"=>$_code))
        );
        $_result = false;
        if($_response["code"] == 200) {
            $_content = json_decode($_response["content"],true);
            if(!$_content["errcode"] && $_content["errmsg"] == "ok") {
                $_result = $_content["template_id"];
                unset($_content);
            }
        }
        return $_result;
    }

    /**
     * 删除模板消息id
     * @param $_template_id
     * @return mixed
     */
    public static function delTplId($_template_id)
    {
        $WeAccount = \WeAccount::create();
        $_response = ihttp_post(
            "https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=".$WeAccount->getAccessToken(),
            json_encode(array("template_id"=>$_template_id))
        );
        $_result = false;
        if($_response["code"] == 200) {
            $_content = json_decode($_response["content"],true);
            if(!$_content["errcode"] && $_content["errmsg"] == "ok") {
                $_result = true;
                unset($_content);
            }
        }
        return $_result;
    }

    /**
     * 发送订阅消息
     * @param string $_openid 用户openid
     * @param string $_template_id 模板消息id
     * @param array $_data 模板消息数据
     * @param string $_url 跳转链接
     * @param array $_min_app 小程序订阅消息通知
     * @return mixed
     */
    public static function sendSubscribeMessage($_openid,$_template_id,$_data,$_url = "",Array $_min_app = array())
    {
        $WeAccount = \WeAccount::create();
        $_send_data = array(
            "touser" => $_openid,
            "template_id" => $_template_id,
            "data" => $_data
        );
        if(!empty($_url)) $_send_data["page"] = $_url;
        if(count($_min_app) > 0) $_send_data["miniprogram"] = $_min_app;
        $_response = ihttp_post(
            "https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend?access_token=".$WeAccount->getAccessToken(),
            json_encode($_send_data)
        );
        $_result = false;
        if($_response["code"] == 200) {
            $_content = json_decode($_response["content"],true);
            if(!$_content["errcode"] && $_content["errmsg"] == "ok") {
                $_result = true;
                unset($_content);
            }
        }
        return $_result;
    }

    /**
     * 获取公众号下的订阅消息类目
     */
    public static function getSubscribeClass()
    {
        $WeAccount = \WeAccount::create();
        $_response = ihttp_get("https://api.weixin.qq.com/wxaapi/newtmpl/getcategory?access_token=".$WeAccount->getAccessToken());
        $_result = false;
        if(isset($_response["content"])) {
            $_content = json_decode($_response["content"],true);
            if($_content["errmsg"] == 'ok' && $_content['errcode'] == 0) {
                $_result = $_content["data"];
            }
        }
        return $_result;
    }

    /**
     * 选用订阅通知模板
     * @param string $_tid 模板标题id
     * @param array $_kid_list  模板关键词列表
     * @param string $_scene_desc 服务场景描述
     * @return mixed
     */
    public static function addSubscribeTemplate($_tid,Array $_kid_list,$_scene_desc)
    {
        $WeAccount = \WeAccount::create();
        $_send_data = array(
            "tid" => $_tid,
            "kidList" => $_kid_list,
            "sceneDesc" => $_scene_desc
        );
        $_response = ihttp_post(
            "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token=".$WeAccount->getAccessToken(),
            $_send_data
        );
        $_result = false;
        if($_response["code"] == 200) {
            $_content = json_decode($_response["content"],true);
            if($_content['errmsg'] == 'ok' && $_content['errcode'] == 0) {
                $_result = $_content["priTmplId"];
                unset($_content);
            }
        }
        return $_result;
    }

    /**
     * 删除订阅消息模板id
     * @param $_tmp_id
     * @return mixed
     */
    public static function delSubscribeTpl($_tmp_id)
    {
        $WeAccount = \WeAccount::create();
        $_send_data = array(
            "priTmplId" => $_tmp_id
        );
        $_response = ihttp_post(
            "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token=".$WeAccount->getAccessToken(),
            $_send_data
        );
        $_result = false;
        if($_response["code"] == 200) {
            $_content = json_decode($_response["content"],true);
            if($_content['errmsg'] == 'ok' && $_content['errcode'] == 0) {
                $_result = true;
                unset($_content);
            }
        }
        return $_result;
    }
}