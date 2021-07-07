<?php
/**
 * 系统关键词操作
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-08
 * Time: 下午 11:44
 */
namespace Kuyuan\WqCore\wx;
class KeywordReply
{
    private $_name = '';  //关键词名字
    private $_keyword = '';  //关键词

    /**
     * KeywordReply constructor.
     * @param string $_keyword  关键词
     * @param string $_name 关键词管理名字
     */
    public function __construct($_keyword,$_name = '')
    {
        $this->_keyword = $_keyword;
        $this->_name = $_name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * 新增关键字回复
     * @param boolean $_is_cover 是否为入口关键词
     * @return boolean
     */
    public function add($_is_cover = false)
    {
        global $_W;
        $_data = array(
            'uniacid'=>$_W['account']['uniacid'],
            'name'=>empty($this->_name) ? $this->_keyword : $this->_name,
            'module'=>$_is_cover ? "cover" : $_W['current_module']['name'],
            'displayorder'=>0,
            'status'=>1
        );
        if(pdo_insert('rule',$_data)){
            $_data['rid'] = pdo_insertid();
            $_data['type'] = 1;
            unset($_data['name']);
            $_data['content'] = $this->_keyword;
            if(pdo_insert('rule_keyword',$_data)){
                return $_data['rid'];
            }else{
                return false;
            }
        }
        return false;
    }

    /**
     * 修改关键字
     * @param $_keyword 旧的关键字
     * @return boolean
     */
    public function update($_keyword)
    {
        global $_W;
        $_where = array(
            'uniacid'=>$_W['account']['uniacid'],
            'name'=>$_keyword,
            'module'=>$_W['current_module']['name']
        );
        $_rule = pdo_get('rule',$_where,array('id'));
        if(pdo_update('rule',array('name'=>$this->_keyword),array('id'=>$_rule['id']))){
            return pdo_update('rule_keyword',array('content'=>$this->_keyword),array('rid'=>$_rule['id']));
        }
        return false;
    }

    /**
     * 是否与其它关键字重复
     */
    public function testRepeat()
    {
        global $_W;
        return pdo_get('rule_keyword',array('uniacid'=>$_W['account']['uniacid'],'content'=>$this->_keyword),array('id'));
    }

    /**
     * 删除关键词
     */
    public function remove()
    {
        global $_W;
        $_keyword = pdo_get('rule_keyword',array('content'=>$this->_keyword,'module'=>$_W['current_module']['name']),array('rid','id'));
        if($_keyword){
            if(pdo_delete('rule',array('id'=>$_keyword['rid']))){
                return pdo_delete('rule_keyword',array('id'=>$_keyword['id']));
            }
        }
        return false;
    }

}