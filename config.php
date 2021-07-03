<?php
defined('IN_IA') or exit('Access Denied');
// 开发模式
!defined("KUYUAN_DEBUG") && define("KUYUAN_DEBUG",true);

// 模块名字
!defined("KUYUAN_NAME") && define("KUYUAN_NAME",'kuyuan_name');

// 缓存标识
!defined("KUYUAN_CACHE_LABEL") && define("KUYUAN_CACHE_LABEL",'kuyuan_');