<?php

namespace queue\Info;

class Stat
{
    const STATUS_WAITING = 'waiting';
    const STATUS_PROCESS = 'process';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    protected static $statInfo = [
        1 => self::STATUS_WAITING,
        2 => self::STATUS_PROCESS,
        3 => self::STATUS_SUCCESS,
        4 => self::STATUS_ERROR
    ];

    /**
     * @param string $stat
     * @return int
     */
    public static function getStatIndexByName(string $stat): int
    {
        return array_search($stat, self::$statInfo);
    }
}