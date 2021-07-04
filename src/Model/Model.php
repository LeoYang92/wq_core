<?php
namespace Kuyuan\WqCore\Model;
use Doctrine\DBAL\DriverManager;

abstract class Model
{
    // 数据库名字
    protected $_table;

    //
    protected $PDO = null;

    public function __construct()
    {
        $this->init();
        $this->setTable();
    }

    /**
     * 配置数据库链接
     */
    private function init()
    {
        include IA_ROOT."/data/config.php";
        $this->PDO = DriverManager::getConnection(
            array(
                'dbname' => $config['db']['master']['database'],
                'user' => $config['db']['master']['username'],
                'password' => $config['db']['master']['password'],
                'host' => $config['db']['master']['host'],
                'driver' => 'pdo_mysql',
            )
        );
        $this->PDO->query("SET NAMES UTF8");
    }

    /**
     * 设置数据表名字
     */
    private function setTable()
    {
        include IA_ROOT."/data/config.php";
        if ($this->_table) return;

        $_w7 = false; // 是否为微擎官方表

        $_filename = end(explode('\\',get_class($this)));
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
        $this->_table = $config['db']['master']['tablepre'].$_filename;
    }

    public static function build()
    {
        $Model = new static();
        return $Model->PDO->createQueryBuilder()->from($Model->_table);
    }
}