<?php
namespace Kuyuan\WqCore\wx;
/**
 * 获取微信支付参数
 * Class PayConfig
 * @package Core\wx
 */
class Pay
{
    /**
     * 获取微信支付参数
     * @param boolean $_sandbox 是否为沙箱环境
     * @return array
     */
    static public function config($_sandbox = true)
    {
        global $_W;
        $_setting = uni_setting();
        load()->func('file');
        $_cert_dir = kstatic_dir("pay_cert/" . ku_uid());
        $_config = array(
            'app_id' => $_W['account']['key'], // 公众号 APPID
            'miniapp_id' => $_W['account']['secret'], // 小程序 APPID
            'mch_id' => $_setting["payment"]["wechat"]["mchid"], // 商户号
            'key' => $_setting["payment"]["wechat"]["apikey"], // 支付key
            'notify_url' => $_W['siteroot'] . 'addons/kuyuan_hb/notify.php', // 支付回调地址
            'cert_client' => $_cert_dir . file_random_name($_cert_dir, "cert"), // optional，退款等情况时用到
            'cert_key' => $_cert_dir . file_random_name($_cert_dir, "cert"),// optional，退款等情况时用到
            'log' => [
                'file' => kstatic_dir("wx_pay_log") . "wx_pay.log",
                'level' => KUYUAN_DEBUG ? "debug" : "info", // 建议生产环境等级调整为 info，开发环境为 debug
            ]
        );
        if (KUYUAN_DEBUG && $_sandbox) $_config["mode"] = "dev";
        return $_config;
    }

    /**
     * 生成证书以及证书内容
     * @param $_cert_client
     * @param $_cert_key
     */
    static public function getCertKey($_cert_client, $_cert_key)
    {
        $_setting = uni_setting();
        $_payment = $_setting["payment"];
        $_cert = authcode($_payment["wechat_refund"]["cert"], "DECODE");
        $_key = authcode($_payment["wechat_refund"]["key"], "DECODE");
        file_put_contents($_cert_client, $_cert);
        file_put_contents($_cert_key, $_key);
    }

    /**
     * 清空支付证书，支付证书不在服务器长时间存放
     * @param static $_cert_client
     * @param static $_cert_key
     */
    static public function clearCertKey($_cert_client, $_cert_key)
    {
        unlink($_cert_client);
        unlink($_cert_key);
    }
}