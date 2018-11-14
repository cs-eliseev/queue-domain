<?php

namespace queue\Helpers;

class PDOConnect extends PDOEasy
{
    const DB_TYPE = 'pgsql';
    const DB_HOST = 'localhost';
    const DB_PORT = '5432';
    const DB_NAME = 'queue';
    const DB_USSER = 'postgres';
    const DB_PASS = '';

    /**
     * @var array
     */
    protected static $connectSettings = [
        'db_type' => self::DB_TYPE,
        'db_host' => self::DB_HOST,
        'db_port' => self::DB_PORT,
        'db_name' => self::DB_NAME,
        'db_user' => self::DB_USSER,
        'db_pass' => self::DB_PASS
    ];

    /**
     * Открываем PDO соединение
     *
     * @param array $connectSettings
     * @return PDOEasy
     */
    public static function connectPDO(array $connectSettings = []): PDOEasy
    {
        $instance = parent::connectPDO(self::$connectSettings);
        $instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $instance;
    }
}