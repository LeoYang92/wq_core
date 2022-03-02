<?php
namespace Kuyuan\WqCore\Model;
use Kuyuan\WqCore\cache\Cache;

/**
 * Class Model
 * @mixin Query
 * @method $this where(mixed $_field, string $op = null, mixed $condition = null) static where语句
 * @method $this whereOr(mixed $_field, string $op = null, mixed $condition = null) static where OR 语句
 * @method $this field(Array $_field = array()) static 请求字段
 * @method $this find() static 请求一条数据
 * @method $this take() static 请求一条数据 不进行getAttr的二次处理
 * @method $this select() static 请求多条数据
 * @method $this cache($_cache, $_label = "") static 是否开启缓存
 * @method $this getAttribute($_field) static 设置需要model的getAttr处理的字段
 * @method $this noGetAttr($_field) static 过滤不经过model的getAttr方法处理
 * @method $this limit($_start, $_end = null) static 请求下标与数量
 * @method $this order(mixed $_field, mixed $_order = "") static 请求列表排序
 * @method mixed value($_field) static 请求一列的某个字段值
 * @method $this key($_key = "") static 设置数据表主键
 * @method mixed count() static 获取数据总数
 * @method mixed sum($_field = "id") static 获取指定字段总和
 * @method $this group($_field) static group by SQL语句
 * @method mixed get($_key_id = "", $_cache = false, $_label = "") static 获取一条数据
 * @method mixed all(Array $_key_ids = array(), $_cache = false, $_label = "") static 获取数据列表
 * @method mixed create(Array $_data) static 新增数据
 * @method mixed createId(Array $_data) static 创建数据并且返回新增的主键id
 * @method mixed createAll(Array $_data) static 批量新增数据
 * @method mixed update(Array $_data) static 修改数据
 * @method mixed addition($_field, $_number = 1) static 数据自增
 * @method mixed minus($_field, $_number = 1) static 数据自减
 * @method mixed delete($_data = "", $_true_delete = false) static 删除数据 $_data 删除的数据id
 * @method mixed sql($_sql) static 自定义sql语句
 * @method mixed bind() static 自定义sql语句绑定的参数
 * @method mixed fetchAll() static 执行自定义sql语句的查找
 * @method mixed column() static 执行自定义sql语句返回指定字段列
 * @method mixed give_delete() static 获取假删除的数据
 */
abstract class Model
{
    // 数据表名字
    protected $_table;

    // 主键
    protected $_key = "id";

    // 新增的id
    public $_id = 0;

    // 缓存标识
    protected $_cache_label;

    // 数据表字段
    protected $_fields;

    // 缓存信息
    protected $_cache = array(false, "");

    // 最终生成的sql语句
    protected $_query;

    // where语句
    protected $_where;

    // order by 语句
    protected $_order;

    // group by 语句
    protected $_group;

    // limit 语句
    protected $_limit;

    // 请求返回的字段
    protected $_field;

    /**
     * 假删除时对应的数据库字段
     * @var string
     */
    protected $_delete_field = "delete_time";

    // sql绑定的数据
    protected $_bind = array();

    // 源数据
    protected $_source = array();

    // 数据对象或者数据对象数组
    protected $data;

    // 不经过getAttr处理的方法字段
    protected $_not_get_attr = array();

    // 需要警告getAttr处理的方法字段
    protected $_get_attribute = array();

    // 绑定参数后缀
    protected $_bind_suffix = 0;

    // 是否获取假删除的字段
    protected $_give_delete = false;

    public function __construct()
    {
        // 设置数据表名字
        $this->setTable();
    }

    /**
     * 设置数据表名字
     */
    private function setTable()
    {
        global $_W;
        if ($this->_table) return;

        $_w7 = false; // 是否为微擎官方表

        $_filename = end(explode('\\', get_class($this)));
        $_filename = substr($_filename, 0, -5);

        // S如果是微擎官方表
        if (strpos($_filename, "W7") === 0) {
            $_w7 = true;
            $_filename = substr($_filename, 2);
        }
        // E如果是微擎官方表

        $_filename = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $_filename);

        if ($_w7) {
            $_filename = substr($_filename, 1);
        } else {
            $_filename = KUYUAN_NAME . $_filename;
        }
        $this->_cache_label = KUYUAN_CACHE_LABEL . $_filename . '_' . $_W["account"]["uniacid"];
        $this->_table = $_filename;
    }

    /**
     * 清空数据表相关缓存
     */
    public static function clearCache()
    {
        $Model = new static();
        Cache::clear($Model->_cache_label);
    }

    /**
     * 获取缓存标识
     */
    public function getCacheLabel()
    {
        return $this->_cache_label;
    }

    public function getTable()
    {
        return $this->_table;
    }

    /**
     * 设置order by排序方式
     */
    public function setOrder($_value)
    {
        $this->_order = $_value;
    }

    /**
     * 获取order by排序方式
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * 设置请求字段
     * @param $_value
     */
    public function setField($_value)
    {
        $this->_field = $_value;
    }

    /**
     * 获取请求字段
     */
    public function getField()
    {
        $_field = $this->_field ? $this->_field : $this->_fields;
        $_str_field = "*";
        if ($_field) {
            $_str_field = "";
            foreach ($_field as $_v) {
                $_pattern = "/(MAX|MIN|COUNT|SUM)\((.*)\)/i";
                $_pattern_as = "/(.*)\s+AS\s+(.*)/i";
                if (preg_match($_pattern, $_v)) {
                    $_v = preg_replace_callback($_pattern, function ($_m) {
                        return $_m[1] . "(`" . $_m[2] . "`)";
                    }, $_v);
                    $_str_field .= $_v . ",";
                } else if (preg_match($_pattern_as, $_v)) {
                    $_v = preg_replace_callback($_pattern_as, function ($_m) {
                        return "`" . $_m[1] . "` AS `" . $_m[2] . "`";
                    }, $_v);
                    $_str_field .= $_v . ",";
                } else {
                    $_str_field .= "`" . $_v . "`,";
                }
            }
            $_str_field = substr($_str_field, 0, -1);
        }
        return $_str_field;
    }

    /**
     * 设置缓存
     * @param Array $_value
     */
    public function setCache($_value)
    {
        $this->_cache = $_value;
    }

    /**
     * 设置是否获取假删除的数据
     * @param boolean $_value
     * @return void
     */
    public function setGiveDelete($_value)
    {
        $this->_give_delete = $_value;
    }

    /**
     * 获取是否返回假删除的数据
     * @return bool
     */
    public function getGiveDelete()
    {
        return $this->_give_delete;
    }

    /**
     * 获取缓存
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * 设置列表请求下标
     * @param $_value
     */
    public function setLimit($_value)
    {
        $this->_limit = $_value;
    }

    /**
     * 获取limit
     * @return mixed
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * 设置where语句
     * @param $_value
     */
    public function setWhere($_value)
    {
        $this->_where = $_value;
    }

    /**
     * 获取where语句
     */
    public function getWhere($_last_where = false)
    {
        $_where = $this->_where;
        if($_last_where && !$this->_give_delete && in_array($this->_delete_field,$this->_fields))
        {
            if($_where) {
                $_where .= " AND ".$this->_delete_field." = :".$this->_delete_field;
            } else {
                $_where = " WHERE ".$this->_delete_field." = :".$this->_delete_field;
            }
            $this->setBind(array_merge($this->getBind(), array($this->_delete_field=>0)));
        }
        return $_where;
    }

    /**
     * 设置绑定参数
     * @param $_value
     */
    public function setBind($_value)
    {
        $this->_bind = $_value;
    }

    /**
     * 获取绑定的参数
     * @return array
     */
    public function getBind()
    {
        return $this->_bind;
    }

    /**
     * 获取group by语句
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * 设置group by 语句
     * @param string $_value
     */
    public function setGroup($_value)
    {
        $this->_group = $_value;
    }

    /**
     * 获取sql
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     *设置sql
     * @param string $_value
     */
    public function setQuery($_value)
    {
        $this->_query = $_value;
    }

    /**
     * 获取数据表主键
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * 设置数据表主键
     * @param $_value
     */
    public function setKey($_value)
    {
        $this->_key = $_value;
    }

    /**
     * 设置字段过滤不执行model类的getAttr
     * @param $_value
     */
    public function setNoGetAttribute($_value)
    {
        if ($_value === false) {
            $this->_not_get_attr = $_value;
        } else {
            $this->_not_get_attr = explode(",", $_value);
        }
    }

    /**
     * 获取字段过滤不执行model类的getAttr
     * @return mixed
     */
    public function getNoGetAttribute()
    {
        return $this->_not_get_attr;
    }

    /**
     * 设置需要执行model类的getAttr字段
     * @param $_value
     */
    public function setGetAttribute($_value)
    {
        $this->_get_attribute = explode(",", $_value);
    }

    /**
     * 获取需要执行model类的getAttr字段
     * @return array
     */
    public function getGetAttribute()
    {
        return $this->_get_attribute;
    }

    /**
     * 设置插入getAttr处理过的源数据
     * @param array $_data 插入的数据
     */
    public function setData(Array $_data)
    {
        $this->_source = array_merge($this->_source, $_data);
    }

    /**
     * 获取绑定参数后缀,where查询时防止绑定参数字段同名
     */
    public function getBindSuffix()
    {
        $this->_bind_suffix++;
        return "_" . $this->_bind_suffix;
    }

    /**
     * 获取getAttr出来过的源数据
     * @param $_field
     * @return mixed
     */
    public function getDate($_field)
    {
        return $this->_source[$_field];
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __call($method, $args)
    {
        array_unshift($args, $this);
        return call_user_func_array(
            array($this->createQuery(), $method),
            $args
        );
    }

    public static function __callStatic($method, $args)
    {
        $Model = new static();
        array_unshift($args, $Model);
        return call_user_func_array(
            array($Model->createQuery(), $method),
            $args
        );
    }

    /**
     * 返回query对象
     */
    private function createQuery()
    {
        return new Query();
    }

    /**
     * 请求完成后清空所有参数，防止new实力化model类时各种参数混淆
     * @return void
     */
    public function clearAllParams()
    {
        $this->_cache_label = "";
        $this->_where = "";
        $this->_cache = array(false,"");
        $this->_query = null;
        $this->_order = null;
        $this->_group = null;
        $this->_limit = null;
        $this->_field = null;
        $this->_delete_field = "delete_time";
        $this->_bind = array();
        $this->_source = array();
        $this->data = null;
        $this->_not_get_attr = array();
        $this->_get_attribute = array();
        $this->_bind_suffix = 0;
        $this->_give_delete = false;
    }
}