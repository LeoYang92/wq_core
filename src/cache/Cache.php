<?php
/**
 * 缓存
 */
namespace Kuyuan\WqCore\cache;
class Cache
{
    /**
     * 设置获取缓存
     * @param string $_key 键
     * @param string $_value 值
     * @param integer $_time 过期时间（秒）
     * @return mixed
     */
    public static function name($_key,$_value = "",$_time = 0)
    {
        if($_key && !$_value && !$_time){

            if($_value === ""){
                // S读
                $_data = cache_load($_key);
                if($_data){
                    if($_data["time"] != 0 && TIMESTAMP > $_data["time"]){
                        cache_delete($_key);
                        return false;
                    } else {
                        return $_data["data"];
                    }
                } else {
                    return array();
                }
                // E读
            } else if($_value === null){
                // S删除
                cache_delete($_key);
                // E删除
            }

        } else {

            // S写
            $_data = array("data"=>$_value,"time"=>$_time ? TIMESTAMP + $_time : 0);
            return cache_write($_key,$_data);
            // E写

        }
    }

    /**
     * 设置数据表相关缓存key键值
     * @param string $_table 数据库缓存标识
     * @param string $_value 相关数据表缓存的标识
     */
    public static function push($_table,$_value)
    {
        $_labels = cache_load($_table);
        if($_labels){
            if(!in_array($_value,$_labels)){
                array_push($_labels,$_value);
            }
        } else {
            $_labels = array($_value);
        }
        cache_write($_table,$_labels);
    }

    /**
     * 清空相关数据表的所有缓存
     * @param string $_table 数据库缓存标识
     */
    public static function clear($_table)
    {
        $_labels = cache_load($_table);
        if($_labels){
            foreach($_labels as $_v){
                cache_delete($_v);
            }
            cache_delete($_table);
        }
    }
}