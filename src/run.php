<?php
/**
 * 模块运行文件
 */
defined('IN_IA') or exit('Access Denied');

$_config_filename = substr(__FILE__,0,strpos(__FILE__,"vendor"))."config.php";
if(is_file($_config_filename)){
    include $_config_filename;
} else {
    include substr(__DIR__,0,-3)."config.php";
}
// 生成（开发）环境，报错级别
error_reporting(E_ALL & ~E_NOTICE);

// 运行错误日志
if (!KUYUAN_DEBUG) {
    ini_set("log_errors", "On");
    ini_set("display_errors", 0);
    ini_set("error_log", kstatic_dir("error_log", true) . "/error.log");
}