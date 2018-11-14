<?php

namespace queue\Info;

class Channel
{
    const CHANNEL_MAIN = 'main';
    const CHANNEL_INFO = 'info';
    const CHANNEL_OFFICIAL = 'official';
    const CHANNEL_DOC = 'doc';

    protected static $channelInfo = [
        1 => self::CHANNEL_MAIN,
        2 => self::CHANNEL_INFO,
        3 => self::CHANNEL_OFFICIAL,
        4 => self::CHANNEL_DOC
    ];

    /**
     * @param string $channel
     * @return int
     */
    public static function getChannelIdByName(string $channel): int
    {
        return array_search($channel, self::$channelInfo);
    }
}