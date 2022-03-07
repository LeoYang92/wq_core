<?php
namespace Kuyuan\WqCore\util;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    /**
     * 创建token
     * @param $_data
     * @return array
     */
    static public function createToken($_data)
    {
        $_result = array();
        $_result['auth_overtime'] = $_data['auth_overtime'] = time() + KUYUAN_JWT_EXPIRE;
        $_result['token'] = JWT::encode($_data,KUYUAN_JWT_KEY,'HS256');
        return $_result;
    }

    /**
     * 解密token
     * @return void
     */
    static public function decodeToken($_token)
    {
        $_result = array();
        try {
            $Result = JWT::decode($_token,new Key(KUYUAN_JWT_KEY,'HS256'));
            if(time() > $Result->auth_overtime) {
                throw new \Exception('token expire',402);
            }
            $_result = json_decode(json_encode($Result),true);
        } catch (\Exception $e) {
            $_result = false;
        }
        return $_result;
    }

    /**
     * 获取
     * @return void
     */
    static public function user($_model_name = '')
    {
        $_auth_models = json_decode(KUYUAN_AUTH,true);
        if(empty($_model_name)) {
            $_model_name = $_auth_models['default'];
        }
        $Result = JWT::decode(
            Util::getHeader('Authorization'),
            new Key(KUYUAN_JWT_KEY,
            'HS256')
        );
        $Model = call_user_func('\Kuyuan\WqCore\util\Util::execClass',$_model_name);
        $_data = $Model->where('id',$Result->id)->find();
        return json_decode(json_encode($_data));
    }
}