<?php

namespace queue\Helpers;

class Arrays
{
    /**
     * @param array  $arr
     * @param string $postfix
     * @return array
     */
    public static function appendPostfix(array $arr, string $postfix): array
    {
        $result = [];

        foreach ($arr as $i) {
            $result[] = $i . $postfix;
        }

        return $result;
    }
}