<?php
namespace Kuyuan\WqCore\Model;

use Kuyuan\WqCore\cache\Cache;

/**
 * 数据库sql语句
 * Class Query
 * @package Util
 */
class Query
{

    /**
     * sqlWhere语句拼接
     * @param $_field
     * @param null $_op
     * @param null $_condition
     * @return mixed
     */
    public function where($_field, $_op = null, $_condition = null)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(is_array($_args[0])) {
            $_where = self::parseArrayWhere($Model,$_args[0],'AND');
        } else {
           $_where = self::parseWhere($Model,$_args[0], $_args[1], $_args[2]);
        }
        $Model->setBind(array_merge($Model->getBind(), $_where["bind"]));
        if (!$_get_where = $Model->getWhere()) {
            $_get_where = " WHERE " . $_where["where"];
        } else {
            $_get_where .= " AND " . $_where["where"];
        }
        $Model->setWhere($_get_where);
        return $Model;
    }

    /**
     * 数据库OR拼接
     * @param $_field
     * @param null $_op
     * @param null $_condition
     * @return mixed
     */
    public function whereOr($_field, $_op = null, $_condition = null)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(is_array($_args[0])) {
            $_where = self::parseArrayWhere($Model,$_args[0],'OR');
        } else {
            $_where = self::parseWhere($Model,$_args[0], $_args[1], $_args[2]);
        }
        $Model->setBind(array_merge($Model->getBind(), $_where["bind"]));
        if (!$_get_where = $Model->getWhere()) {
            $_get_where = " WHERE " . $_where["where"];
        } else {
            $_get_where .= " OR " . $_where["where"];
        }
        $Model->setWhere($_get_where);
        return $Model;
    }

    /**
     * 解析where语句
     * @param object $Model 数据表对象
     * @param $_field
     * @param null $_op
     * @param null $_condition
     * @return array
     */
    private function parseWhere($Model,$_field, $_op = null, $_condition = null)
    {
        $_where = "";
        $_bind = array();
        if (!$_op && gettype($_op) != 'string' && gettype($_op) != 'integer' && !$_condition) {
            $GLOBALS["kuyuan_where_binds"] = array();
            $_field = preg_replace_callback("/(\w+)\s?(=||>||<||>=)\s?(\w+)\s?(AND||OR)/i", function ($_matches) use ($Model) {
                $_bind_param = $_matches[1].$Model->getBindSuffix();
                $GLOBALS["kuyuan_where_binds"][$_bind_param] = $_matches[3];
                return $_matches[1] . " ".$_matches[2]." :" . $_bind_param." ".$_matches[4];
            }, $_field);
            $_field = preg_replace_callback("/(\w+)\s+LIKE\s+\'(.+)\'/i", function ($_matches) {
                $GLOBALS["kuyuan_where_binds"][$_matches[1]] = $_matches[2];
                return $_matches[1] . " LIKE :".$_matches[1];
            }, $_field);
            $_where = $_field;
            $_bind = $GLOBALS["kuyuan_where_binds"];
            unset($GLOBALS["kuyuan_where_binds"]);
        } else if ((gettype($_op) == 'string' || gettype($_op) == 'integer') && !$_condition) {
            $_bind_param = $_field.$Model->getBindSuffix();
            $_where = $_field . " = :" . $_bind_param;
            $_bind = array($_bind_param => $_op);
        } else if ($_op && $_condition) {
            // 单独处理IN
            if(strtolower(trim($_op)) === "in"){
                if(!is_array($_condition)){
                    $_condition = explode(",",$_condition);
                }
                $_condition_str = "(";
                foreach($_condition as $_k=>$_v){
                    $_condition_str .= ":in_".$_k.",";
                    $_bind["in_".$_k] = $_v;
                }
                $_condition_str = substr($_condition_str,0,-1);
                $_condition_str .= ")";
                $_condition = $_condition_str;
                unset($_condition_str);
                $_where = $_field." IN ".$_condition;
            } else {
                $_bind_param = $_field.$Model->getBindSuffix();
                $_where = $_field . " " . $_op . " :" . $_bind_param;
                $_bind = array($_bind_param => $_condition);
            }
        }
        return array("where" => $_where, "bind" => $_bind);
    }

    /**
     * 解析where and 语句
     * @param $Model
     * @param $_field
     * @param string $_type
     * @return array
     */
    private function parseArrayWhere($Model,$_field,$_type = 'AND')
    {
        $_where = "";
        $_bind = array();
        foreach($_field as $_k=>$_v) {
            $_keys = explode(' ',$_k);
            $_bind_param = $_keys[0].$Model->getBindSuffix();
            if(count($_keys) > 1) {
                $_where .= $_keys[0] ." ".$_keys[1]." :".$_bind_param." ".$_type." ";
                $_bind[$_bind_param] = $_v;
            } else {
                $_where .= $_k ." = :".$_bind_param." ".$_type." ";
                $_bind[$_bind_param] = $_v;
            }
        }
        $_where = substr($_where,0,-(strlen($_type) + 2));
        return array("where" => $_where, "bind" => $_bind);
    }

    /**
     * 请求数据库字段
     * @param array $_field
     * @return mixed
     */
    public function field($_field)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        $_query = "";
        $_field = $_args[1];
        $Model->setField($_field);
        return $Model;
    }

    /**
     * 查询一条数据
     */
    public function find()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        $_query = "
                        SELECT 
                                " . $Model->getField() . " 
                        FROM " . tablename($Model->getTable()) . "
                        " . $Model->getWhere(true) . " 
                        LIMIT 1
                       ";
        $Model->setQuery($_query);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("find",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetch($Model->getQuery(), $Model->getBind());
        }
        // E获取设置缓存

        // S执行属性二次处理
        if($_result){
            $_result = self::getAttr($Model,$_result);
        }
        // E执行属性二次处理
        $Model->clearAllParams();
        return $_result;
    }


    /**
     * 与find的区别是内部不进行getNameAttr的处理
     */
    public function take()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        $_query = "
                        SELECT 
                                " . $Model->getField() . " 
                        FROM " . tablename($Model->getTable()) . "
                        " . $Model->getWhere(true) . "
                        LIMIT 1
                       ";
        $Model->setQuery($_query);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("find",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetch($Model->getQuery(), $Model->getBind());
        }
        // E获取设置缓存
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * 设置getAttr处理字段
     * @param object $Model 模型对象
     * @param array $_data 要处理的数据
     * @return array
     */
    private function getAttr($Model,Array $_data)
    {
        $_no_attrs = $Model->getNoGetAttribute();
        if($_no_attrs === false) return $_data;
        $_get_attrs = $Model->getGetAttribute();
        if($_data && count($_data) > 0){
            $_methods = get_class_methods($Model);
            foreach($_methods as $_v){
                if(preg_match("/^get(.+)Attr$/",$_v,$_matches)){
                    $_key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $_matches[1]));
                    if(count($_get_attrs) > 0) {
                        if(!in_array($_key,$_get_attrs)) {
                            continue;
                        }
                    } else {
                        if(in_array($_key,$_no_attrs)){
                            continue;
                        }
                    }
                    $Model->setData(array($_key => $_data[$_key]));
                    $_data[$_key] = call_user_func_array(array($Model,$_v),array($_data[$_key],$_data));
                }
            }
        }
        return $_data;
    }

    /**
     * 新增数据-设置自动处理setAttr数据库字段，并且过滤掉数据库不需要的字段
     * @param $Model
     * @param array $_data
     * @return mixed
     */
    private function setAttr($Model,Array $_data)
    {
        $_methods = get_class_methods($Model);
        foreach($_methods as $_v){
            if(preg_match("/^set(.+)Attr$/",$_v,$_matches)){
                $_key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $_matches[1]));
                $_data[$_key] = call_user_func_array(array($Model,$_v),array($_data[$_key],$_data));
            }
        }
        return $_data;
    }

    /**
     * 修改数据-设置自动处理setAttr数据库字段，并且过滤掉数据库不需要的字段
     * @param $Model
     * @param array $_data
     * @return mixed
     */
    private function updateSetAttr($Model,Array $_data)
    {
        $_methods = get_class_methods($Model);
        foreach($_methods as $_v){
            if(preg_match("/^set(.+)Attr$/",$_v,$_matches)){
                $_key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $_matches[1]));
                if(isset($_data[$_key]) || self::testUpdateRelations($Model,$_data,$_key)){
                    $_data[$_key] = call_user_func_array(array($Model,$_v),array($_data[$_key],$_data));
                }
            }
        }
        return $_data;
    }

    /**
     * 检测数据修改中是否包含数据库字段相关的数据键，如果包含怎运行修改数据库字段
     * @param Object $Model
     * @param array $_data
     * @param string $_key setAttr字段
     * @return mixed
     */
    private function testUpdateRelations($Model,Array $_data,$_key)
    {
        $_result = true;
        if(!!!$Model->_relations || !isset($Model->_relations[$_key])) $_result = false;
        if($_result) {
            $_relations = $Model->_relations[$_key];
            foreach($_relations as $_v) {
                if(!isset($_data[$_v])){
                    $_result = false;
                    break;
                }
            }
        }
        return $_result;
    }

    /**
     *  新增修改数据时过滤无用字段
     * @param $Model
     * @param $_data
     * @return mixed
     */
    private function filtrationField($Model,$_data)
    {
        $_temp_fields = array_flip($Model->_fields);
        $_diff = array_diff_key($_data,$_temp_fields);
        foreach ($_diff as $_k=>$_v) {
            unset($_data[$_k]);
        }
        unset($_temp_fields,$_diff);
        return $_data;
    }

    /**
     * 请求数据下标与数量
     * @param $_start
     * @param null $_end
     * @return mixed
     */
    public function limit($_start,$_end = null)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_limit = " LIMIT ".$_args[0];
        if($_args[1]) $_limit .= ",".$_args[1];
        $Model->setLimit($_limit);
        return $Model;
    }

    /**
     * order by 排序
     * @param $_field
     * @param $_order
     * @return mixed
     */
    public function order($_field,$_order = null)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_order = self::parseOrder($_args[0],$_args[1]);
        if(!$_get_order = $Model->getOrder()){
            $_get_order = "ORDER BY ".$_order["order"];
        } else {
            $_get_order .= ",".$_order["order"];
        }
        $Model->setOrder($_get_order);
        return $Model;
    }

    /**
     * 解析Order by 排序方式
     * @param string $_field
     * @param string $_order
     * @return mixed
     */
    private function parseOrder($_field,$_order)
    {
        $_result = "";
        if(!$_order){
            $_result = $_field;
        } else {
            $_result = $_field." ".strtoupper($_order);
        }
        return array("order"=>$_result);
    }

    /**
     * group by 语句
     * @param $_field
     * @return mixed
     */
    public function group($_field)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(!$_get_group = $Model->getGroup()){
            $_get_group = "GROUP BY ".$_args[0];
        } else {
            $_get_group .= $_args[0];
        }
        $Model->setGroup($_get_group);
        return $Model;
    }

    /**
     * 查询多条数据
     */
    public function select()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        $_query = "
                        SELECT 
                                " . $Model->getField() . " 
                        FROM " . tablename($Model->getTable()) . "
                        " . $Model->getWhere(true) . "
                        ".$Model->getGroup()."
                        ".$Model->getOrder()."
                        ".$Model->getLimit()."
                       ";
        $Model->setQuery($_query);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("select",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetchall($Model->getQuery(), $Model->getBind());
        }
        // E获取设置缓存
        if($_result){
            foreach ($_result as $_k=>$_v){
                $_result[$_k] = self::getAttr($Model,$_v);
            }
        }
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * @param $_key_id
     * @param bool $_cache
     * @param string $_label
     * @return mixed
     */
    public function get($_key_id = "",$_cache = false,$_label = "")
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model = $Model::cache($_args[1],$_args[2]);
        if($_key_id){
            $Model = $Model->where($Model->getKey(),$_args[0]);
        }
        $Model->clearAllParams();
        return $Model->find();
    }


    /**
     * 获取多条数据
     * @param array $_key_ids
     * @param bool $_cache
     * @param string $_label
     * @return mixed
     */
    public function all($_key_ids = array(),$_cache = false,$_label = "")
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model = $Model::cache($_args[1],$_args[2]);
        $_data = array();
        if(count($_args[0]) <= 0){
            $_data = $Model->select();
        } else {
            foreach ($_args[0] as $_v) {
                array_push($_data,(array) $Model->get($_v));
            }
        }
        $Model->clearAllParams();
        return $_data;
    }

    /**
     * 获取一条数据的某个字段
     * @param string $_field 字段
     * @return mixed
     */
    public function value($_field)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_sql = "SELECT 
						`$_args[0]`
					FROM
						".tablename($Model->getTable())."
					    ".$Model->getWhere(true)." 
					 LIMIT 1
				";
        $Model->setQuery($_sql);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("value",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetchcolumn($Model->getQuery(), $Model->getBind());
        }
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * 设置Model缓存状态
     * @param bool|integer $_cache 是否启用缓存或者缓存时间
     * @param string $_label 缓存标识
     * @return mixed
     */
    public function cache($_cache = true,$_label = "")
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model->setCache(array($_args[0],$_args[1]));
        return $Model;
    }

    /**
     * 设置Model是否获取已经假删除的数据
     * @return mixed
     */
    public function give_delete()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        $Model->setGiveDelete(true);
        return $Model;
    }

    /**
     * 设置不经过getAttr二次处理的方法
     * @param boolean $_method
     * @return mixed
     */
    public function noGetAttr($_method = false)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model->setNoGetAttribute($_args[0]);
        return $Model;
    }

    /**
     * 需要经过getAttr二次处理的方法字段
     * @param $_method
     * @return mixed
     */
    public function getAttribute($_method)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model->setGetAttribute($_args[0]);
        return $Model;
    }

    /**
     * 数据总数
     */
    public function count()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_sql = "SELECT 
						COUNT(`".$Model->getKey()."`)
					FROM
						".tablename($Model->getTable())."
					    ".$Model->getWhere(true)." 
					    ".$Model->getLimit()."
				";
        $Model->setQuery($_sql);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("count",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetchcolumn($Model->getQuery(), $Model->getBind());
        }
        // E获取设置缓存
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * 某个字段的总和
     * @param string $_field
     * @return mixed
     */
    public function sum($_field = "id")
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(!$_args[0]) $_args[0] = "id";
        $_sql = "SELECT 
						SUM(`".$_args[0]."`)
					FROM
						".tablename($Model->getTable())."
					    ".$Model->getWhere(true)."
				";
        $Model->setQuery($_sql);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("sum",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetchcolumn($Model->getQuery(), $Model->getBind());
        }
        // E获取设置缓存
        $Model->clearAllParams();
        return $_result ? $_result : 0;
    }

    /**
     * 设置数据表主键
     * @param string $_key
     * @return mixed
     */
    public function key($_key = "")
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model->setKey($_args[0]);
        return $Model;
    }

    /**
     * 设置缓存
     * @param string $_type 类型
     * @param Object $Model Model对象
     * @param int $_time 缓存时间
     * @param string $_label 自定义标识
     * @return mixed
     */
    private function setCache($_type,$Model,$_time,$_label = "")
    {
        $_cache_label = md5(KUYUAN_CACHE_LABEL.$Model->getQuery().json_encode($Model->getBind()));

        if($_label == ""){
            $_result = Cache::name($_cache_label);
            if(!$_result && (is_array($_result) && count($_result) <= 0)) {
                $_result = self::sqlMethodResult($Model,$_type);
                if($_result){
                    Cache::name($_cache_label,$_result,$_time);
                }
            }
            // S设置缓存以及数据表缓存标识
            $_table_cache_label = $Model->getCacheLabel();
            Cache::push($_table_cache_label,$_cache_label);
            // E设置缓存以及数据表缓存标识
        } else {
            $_result = Cache::name($_label);
            if(!$_result){
                $_result = self::sqlMethodResult($Model,$_type);
                if($_result) Cache::name($_label,$_result,$_time);
            }
        }

        return $_result;
    }

    /**
     * 获取缓存通过不同数据库查询关键字获取数据
     * @param object $Model model对象
     * @param string $_type 查询类型
     * @return mixed
     */
    private function sqlMethodResult($Model,$_type)
    {
        $_result = false;
        switch ($_type){
            case "find":
                $_result = pdo_fetch($Model->getQuery(), $Model->getBind());
                break;
            case "select":
                $_result = pdo_fetchall($Model->getQuery(), $Model->getBind());
                break;
            case "count":
            case "sum":
            case "value":
                $_result = pdo_fetchcolumn($Model->getQuery(), $Model->getBind());
                break;
        }
        return $_result;
    }

    /**
     * 新增数据
     * @param $_data
     * @return bool
     */
    public function create($_data)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_insert_data = self::setAttr($Model,$_args[0]);
        $_insert_data = self::filtrationField($Model,$_insert_data);
        $_result = pdo_insert($Model->getTable(),$_insert_data);
        $Model->clearAllParams();
        if($_result){
            $Model::clearCache();
            $Model->id = pdo_insertid();
            return $Model;
        } else {
            return false;
        }
    }

    /**
     * 新增数据并且返回新增id
     * @param $_data
     * @return int 新增的id
     */
    public function createId($_data)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_insert_data = self::setAttr($Model,$_args[0]);
        $_insert_data = self::filtrationField($Model,$_insert_data);
        $_result = pdo_insert($Model->getTable(),$_insert_data);
        $Model->clearAllParams();
        if($_result){
            $Model->id = pdo_insertid();
            $Model::clearCache();
            return $Model->id;
        } else {
            return false;
        }
    }

    /**
     * 批量新增数据
     * @param $_data
     * @return bool
     */
    public function createAll($_data)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_insert_ids = array();
        $_insert_data = array();
        foreach($_args[0] as $_v){
            $_insert_data = self::setAttr($Model,$_v);
            $_insert_data = self::filtrationField($Model,$_insert_data);
            if(pdo_insert($Model->getTable(),$_insert_data)){
                array_push($_insert_ids,pdo_insertid());
            }
        }
        unset($_insert_data);
        $Model->clearAllParams();
        if(count($_insert_ids) > 0){
            $Model::clearCache();
            $Model->id = $_insert_ids;
            return $Model;
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param array $_data
     * @return bool 是否修改成功
     */
    public function update($_data)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $_update_data = self::updateSetAttr($Model,$_args[0]);
        $_update_data = self::filtrationField($Model,$_update_data);
        $_sql = "
                UPDATE 
                    ".tablename($Model->getTable())."
                    ".self::parseUpdateData($Model,$_update_data)."
                    ".$Model->getWhere()." 
                ";
        $Model->setQuery($_sql);
        $Model::clearCache();
        $_bind = $Model->getBind();
        $Model->clearAllParams();
        return pdo_query($_sql,$_bind);
    }

    /**
     * 解析修改数据的sql语句
     * @param $Model
     * @param $_data
     * @return false|string
     */
    private function parseUpdateData($Model,$_data)
    {
        $_set_sql = "SET ";
        $_bind_array = array();
        foreach($_data as $_k => $_v){
            $_bind_key = "u_".$_k;
            $_set_sql .= $_k ." = :".$_bind_key.",";
            $_bind_array[$_bind_key] = $_v;
        }
        unset($_data);
        $Model->setBind(array_merge($Model->getBind(),$_bind_array));
        $_set_sql = substr($_set_sql,0,-1);
        return $_set_sql;
    }

    /**
     * 数据字段自增
     * @param string $_field 字段
     * @param int $_number 字段自增数量
     * @return mixed
     */
    public function addition($_field,$_number = 1)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(!isset($_args[1])) $_args[1] = 1;
        $_sql = "
            UPDATE
                    ".tablename($Model->getTable())."
                    ".self::parseAddMinData($_args[0],$_args[1],"+")."
                    ".$Model->getWhere()." 
        ";
        $Model->setQuery($_sql);
        $Model::clearCache();
        $Model->clearAllParams();
        return pdo_query($_sql,$Model->getBind());
    }

    /**
     * 数据字段自减
     * @param string $_field 操作字段
     * @param int $_number 自减数
     * @return mixed
     */
    public function minus($_field,$_number = 1)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(!isset($_args[1])) $_args[1] = 1;
        $_sql = "
            UPDATE
                    ".tablename($Model->getTable())."
                    ".self::parseAddMinData($_args[0],$_args[1],"-")."
                    ".$Model->getWhere()."
        ";
        $Model->setQuery($_sql);
        $Model::clearCache();
        $Model->clearAllParams();
        return pdo_query($_sql,$Model->getBind());
    }

    /**
     * 解析返回自增自减set语句
     * @param $_field
     * @param $_number
     * @param string $_type
     * @return string
     */
    private function parseAddMinData($_field,$_number,$_type="+")
    {
        return "SET `$_field` = `$_field`$_type$_number";
    }

    /**
     * 删除数据
     * @param array|int $_data 删除的主键id
     * @param bool $_true_delete 是否真的删除
     * @return bool 删除状态
     */
    public function delete($_data,$_true_delete = false)
    {
        $_result = false;
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        if(count($_args) <= 0){
            // 不传参数
            $_result = $Model->update(
                array($Model->_delete_field => TIMESTAMP)
            );

        } else if(is_bool($_args[0])){

            // 第一个参数是个布尔值
            if($_args[0]){
                $_result = self::trueDelete($Model);
            } else {
                $_result = $Model->update(
                    array($Model->_delete_field => TIMESTAMP)
                );
            }

        } else if(is_int($_args[0]) || is_string($_args[0]) || is_array($_args[0])){
            $_key = "";
            $_true_delete = $_args[1];
            if(is_int($_args[0]) || is_string($_args[0])){

                // 直接传id
                $_key = $_args[0];
                $Model->where($Model->getKey(),$_key);
                if($_true_delete){
                    $_result = self::trueDelete($Model);
                } else {
                    $_result = $Model->update(
                        array($Model->_delete_field => TIMESTAMP)
                    );
                }


            } else {

                // 数组
                if($_true_delete){
                    $Model->where($Model->getKey(),"in",$_args[0]);
                    $_result = self::trueDelete($Model);
                } else {
                    foreach($_args[0] as $_v){
                        $Model->where($Model->getKey(),$_v)->update(array($Model->_delete_field => TIMESTAMP));
                    }
                    $_result = true;
                }

            }
        }
        if($_result) $Model::clearCache();
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * 真删除
     * @param object model对象
     * @return bool
     */
    private function trueDelete($Model)
    {
        $_sql = "
                 DELETE FROM
                        ".tablename($Model->getTable())."
                        ".$Model->getWhere()."
                ";
        $Model->setQuery($_sql);
        return pdo_query($_sql,$Model->getBind());
    }

    /**
     * 自定义sql语句
     * @param string $_sql sql语句
     * @return mixed
     */
    public function sql($_sql)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model->setQuery($_args[0]);
        return $Model;
    }

    /**
     * 自定义sql语句的绑定参数
     * @param array $_bind 绑定的参数数组
     * @return mixed
     */
    public function bind($_bind)
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        $Model->setBind($_args[0]);
        return $Model;
    }

    /**
     * 执行查找自定义sql语句
     */
    public function fetchAll()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("select",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetchall($Model->getQuery(),$Model->getBind());
        }
        if($_result){
            foreach ($_result as $_k=>$_v){
                $_result[$_k] = self::getAttr($Model,$_v);
            }
        }
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * 执行查找自定义sql语句，查找一个数据
     */
    public function fetch()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("find",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetch($Model->getQuery(),$Model->getBind());
        }
        if($_result){
            $_result = self::getAttr($Model,$_result);
        }
        $Model->clearAllParams();
        return $_result;
    }

    /**
     * 执行自定义sql获取数据库指定字段
     */
    public function column()
    {
        $_args = func_get_args();
        $Model = $_args[0];
        array_shift($_args);
        list($_cache,$_label) = $Model->getCache();
        // S获取设置缓存
        if($_cache){
            $_result = self::setCache("count",$Model,$_cache === true ? 0 : $_cache,$_label);
        } else {
            $_result = pdo_fetchcolumn($Model->getQuery(), $Model->getBind());
        }
        // E获取设置缓存
        return $_result;
    }
}