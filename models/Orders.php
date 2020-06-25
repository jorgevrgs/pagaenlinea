<?php
/**
* 2018 Jorge Vargas.
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <https://addons.prestashop.com/contact-form.php?id_product=31085>
* @copyright 2007-2018 Jorge Vargas
*
* @see      http://addons.prestashop.com/es/2_community?contributor=3167
*
* @license   End User License Agreement (EULA)
*
* @version   2.0
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

class Models_Orders extends ObjectModel
{
    /**
     * @var int
     */
    public $id_order;

    /**
     * @var int
     */
    public $id_transaction;

    /**
     * @var string
     */
    public $id_terminal;

    /**
     * @var string
     */
    public $id_acquirer;

    /**
     * @var bool
     */
    public $is_test;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'order_pagaenlinea',
        'primary' => 'id_order_pagaenlinea',
        'fields' => array(
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ),
            'id_transaction' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ),
            'id_terminal' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ),
            'id_acquirer' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ),
            'is_test' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ),
        ),
    );

    public static function getTransactionByIdOrder($id_order)
    {
        $cache_id = "pagaenlinea_getTransactionByIdOrder_{$id_order}";

        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(self::$definition['table']);
            $sql->where('id_order = '.(int) $id_order);

            $result = Db::getInstance()->getRow($sql);
            Cache::store($cache_id, $result);
        }

        return Cache::retrieve($cache_id);
    }

    public static function getIdTransactionByIdOrder($id_order)
    {
        $cache_id = "pagaenlinea_getIdTransactionByIdOrder_{$id_order}";

        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('id_transaction');
            $sql->from(self::$definition['table']);
            $sql->where('id_order = '.(int) $id_order);

            $result = Db::getInstance()->getValue($sql);
            Cache::store($cache_id, $result);
        }

        return Cache::retrieve($cache_id);
    }

    public static function getTransactionByIdTransaction($id_transaction)
    {
        $cache_id = "pagaenlinea_getTransactionByIdTransaction_{$id_transaction}";

        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from(self::$definition['table']);
            $sql->where('id_transaction = '.(int) $id_transaction);

            $result = Db::getInstance()->getRow($sql);
            Cache::store($cache_id, $result);
        }

        return Cache::retrieve($cache_id);
    }

    public static function getIdOrderByIdTransaction($id_transaction)
    {
        $cache_id = "pagaenlinea_getIdOrderByIdTransaction_{$id_transaction}";

        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('id_order');
            $sql->from(self::$definition['table']);
            $sql->where('id_transaction = '.(int) $id_transaction);

            $result = Db::getInstance()->getValue($sql);
            Cache::store($cache_id, $result);
        }

        return Cache::retrieve($cache_id);
    }

    /**
     * @param int $id_order
     * @param int $id_transaction
     * @param string $id_terminal
     * @param string $id_acquirer
     * @param bool $is_test
     *
     * @uses Validate::isUnsidnedId()
     * @uses Db::getInstance()
     */
    public static function addTransaction($id_order, $id_transaction, $id_terminal, $id_acquirer, $is_test = false)
    {
        if (!Validate::isUnsidnedId($id_order)
        || $id_order < 0
        || !Validate::isUnsidnedId($id_transaction)
        || $id_transaction < 0) {
            return false;
        }

        return Db::getInstance()->insert(
            self::$definition['table'],
            array(
                'id_order' => (int) $id_order,
                'id_transaction' => (int) $id_transaction,
                'id_terminal' => pSQL($id_terminal),
                'id_acquirer' => pSQL($id_acquirer),
                'is_test' => (bool) $is_test,
            ),
            false,
            true,
            Db::REPLACE
        );
    }
}
