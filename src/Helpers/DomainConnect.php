<?php

namespace queue\Helpers;

class DomainConnect
{
    /**
     * @param string $url
     * @return resource
     */
    public static function connectDomain(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_exec($ch);

        return $ch;
    }
}