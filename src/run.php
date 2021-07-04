<?php
/**
 * 模块运行文件
 */
defined('IN_IA') or exit('Access Denied');

// 生成（开发）环境，报错级别
error_reporting(E_ALL & ~E_NOTICE);

// 运行错误日志
if (!KUYUAN_DEBUG) {
    ini_set("log_errors", "On");
    ini_set("display_errors", 0);
    ini_set("error_log", kstatic_dir("error_log", true) . "/error.log");
}