<?php
defined('IN_IA') or exit('Access Denied');
class kuyuan_nameModule extends WeModule {
    public function welcomeDisplay($menus = array())
    {
        global $_W;
        header("location:" . wurl('site/entry/manage', array('m' => 'kuyuan_name')));
        exit;
    }
}