<?php
namespace Kuyuan\WqCore\util;
class DB
{
    /**
     * 数据库开启事务
     */
    public static function begin()
    {
        pdo()->begin();
    }

    /**
     * 数据库提交事务
     */
    public static function commit()
    {
        pdo()->commit();
    }

    /**
     * 数据库回滚
     */
    public static function back()
    {
        pdo()->rollBack();
    }

    /**
     * 返回数据表名字
     * @param $_name
     * @return mixed
     */
    public static function tableName($_name)
    {
        return tablename(KUYUAN_NAME."_".$_name);
    }

}