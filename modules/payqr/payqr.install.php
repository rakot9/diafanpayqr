<?php
/**
 * Установка модуля
 *
 * @package    Diafan.CMS
 * @author     diafan.ru
 * @version    5.4
 * @license    http://cms.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2014 OOO «Диафан» (http://diafan.ru)
 */

if (! defined('DIAFAN'))
{
    include dirname(dirname(dirname(__FILE__))).'/includes/404.php';
}

class Payqr_install extends Install
{
    /**
     * @var string название
     */
    public $title = "Платежный сервис PayQR";

    /**
     * @var array таблицы в базе данных
     */
    public $tables = array(
		array(
			"name" => "payqr_event",
                        "comment" => "События",
			"fields" => array(
                                        array(
                                            "name" => "id",
                                            "type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
                                            "comment" => "идентификатор",
                                        ),
                                        array(
                                            "name" => "invoice_id",
                                            "type" => "VARCHAR(255) NOT NULL",
                                            "comment" => "",
                                                        ),
                                        array(
                                            "name" => "order_id",
                                            "type" => "TEXT NULL DEFAULT NULL",
                                            "comment" => "",
                                        ),
                                        array(
                                            "name" => "amount",
                                            "type" => "decimal(10, 2) DEFAULT NULL",
                                            "comment" => "",
                                        ),
                                        array(
                                            "name" => "data",
                                            "type" => "TEXT DEFAULT NULL",
                                            "comment" => "",
                                        ),
                                        array(
                                            "name" => "datetime",
                                            "type" => "timestamp DEFAULT CURRENT_TIMESTAMP",
                                            "comment" => "",
                                        ),
                                        array(
                                            "name" => "is_paid",
                                            "type" => "TINYINT(1) NULL DEFAULT 0",
                                            "comment" => "",
                                        ),

                                ),
                                "keys" => array(
                                        "PRIMARY KEY (id)",
                                ),
		),
	);

    /**
     * @var array записи в таблице {modules}
     */
    public $modules = array(
        array(
            "name" => "payqr",
            "admin" => true,
            "site" => true,
            "site_page" => false,
        ),
    );

    /**
     * @var array меню административной части
     */
    public $admin = array(
        array(
            "name" => "Платежный сервис PayQR",
            "rewrite" => "payqr",
            "group_id" => "1",
            "sort" => 5,
            "act" => true,
            "children" => array(
                array(
                    "name" => "Настройки",
                    "rewrite" => "payqr/config",
                ),
            )
        ),
    );
}