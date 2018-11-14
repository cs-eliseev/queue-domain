<?php

namespace queue\Helpers;

class Notify
{
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_TEXT_PLAIN = 'text/plain';

    const MIME_VERSION = '1.0';
    const CHARSET_UTF_8 = 'utf-8';

    const HEADER_SPACER = "\r\n";

    const PATH_DIR_MAIL_TMPL = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmpl';
    const FILE_EXTENSION = 'tpl';

    /**
     * Отправка сообщений
     *
     * @param string $email
     * @param string $subject
     * @param $message
     * @param string $contentType
     * @param string $mimeVersion
     * @param string $charset
     * @return bool
     */
    public static function send(string $emails, string $subject, $message, $contentType = self::CONTENT_TYPE_TEXT_HTML,
                                string $mimeVersion = self::MIME_VERSION, string $charset = self::CHARSET_UTF_8): bool
    {
        $headers  = 'MIME-Version: ' . $mimeVersion . self::HEADER_SPACER
                  . 'Content-type: ' . $contentType . '; charset=' . $charset. self::HEADER_SPACER;

        return mail($emails, $subject, print_r($message, true), $headers);
    }

    /**
     * Получение контента шаблона по его имени
     *
     * @param string $tmplName
     * @return string
     */
    public static function getMailContentByTmplName(string $tmplName): ?string
    {
        return file_get_contents(self::PATH_DIR_MAIL_TMPL . DIRECTORY_SEPARATOR . $tmplName . '.' . self::FILE_EXTENSION) ?? null;
    }

    /**
     * Подстановка параметров в шаблон
     *
     * @param string $tmplName
     * @param array $param
     * @return string
     */
    public static function setParamsMailByTmplName(string $tmplName, array $param): string
    {
        return preg_replace(array_keys($param), array_values($param), self::getMailContentByTmplName($tmplName));
    }
}