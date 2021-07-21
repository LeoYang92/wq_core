<?php
namespace Kuyuan\WqCore\util;
class Date
{
    /**
     * 通过一个时间戳返回简单的时间格式
     * 当日时间返回 12:30 当年时间返回 08-10 12:30 其他返回 2021-08-10 12:30
     * @param $_timestamp
     * @return string
     */
    public static function shortFormat($_timestamp)
    {
        $_format = "Y-m-d H:i:s";
        $_today_time = strtotime(date("Y-m-d"));
        if($_timestamp > $_today_time) {
            $_format = "H:i:s";
        } else if(date("Y") == date("Y",$_timestamp)) {
            $_format = "m-d H:i:s";
        }
        return date($_format,$_timestamp);
    }
}