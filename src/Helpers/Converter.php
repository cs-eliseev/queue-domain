<?php

namespace queue\Helpers;

class Converter
{
    /**
     * @param int $value
     * @param int $dec
     * @return float
     */
    public static function bytesToMb(int $value, int $dec = 2): float
    {
        return round($value / pow(1024, 2), $dec);
    }
}