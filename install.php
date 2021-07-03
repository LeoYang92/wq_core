<?php
//$_install_sql = "
//    CREATE TABLE IF NOT EXISTS `ims_kuyuan_red_batch` (
//        `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
//        `red_id` INT ( 10 ) UNSIGNED NOT NULL COMMENT '红包id',
//        `create_count` SMALLINT ( 5 ) UNSIGNED NOT NULL COMMENT '生成红包的批数（没生成一次减去一次）',
//        `already_create_count` SMALLINT ( 5 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT '已经生成的批量数',
//        `last_count` SMALLINT ( 5 ) UNSIGNED NOT NULL COMMENT '最后一次生成红包的数量',
//        `money` DECIMAL ( 10, 2 ) UNSIGNED NOT NULL COMMENT '每次生成的金额',
//        `last_money` DECIMAL ( 10, 2 ) UNSIGNED NOT NULL COMMENT '最后一次生成的金额',
//    PRIMARY KEY ( `id` )
//    ) ENGINE = MyISAM DEFAULT CHARSET = utf8;
//";
//pdo_run($_install_sql);
