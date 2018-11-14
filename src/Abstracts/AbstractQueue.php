<?php

namespace queue\Abstracts;

use queue\Helpers\Converter;
use queue\Helpers\Notify;
use queue\Helpers\PDOConnect;

abstract class AbstractQueue
{
    /**
     * @var PDOConnect
     */
    protected $db;

    /**
     * @var string
     */
    protected $logName = 'queue.log';

    /**
     * @var string
     */
    protected $logPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs';

    /**
     * @var string
     */
    protected $scriptName;

    /**
     * @var string
     */
    protected $stopFile = '/tmp/queue_daemon_restart.txt';

    /**
     * @var float|int
     */
    protected $processLimit;

    /**
     * @var float|int
     */
    protected $usageLimit;

    /**
     * @var int
     */
    protected $sleepTime = 10;

    /**
     * @var string
     */
    protected $emailSupport = 'a.k.eliseyev@gmail.com';

    public function __construct()
    {
        $this->scriptName = $_SERVER['SCRIPT_FILENAME'];

        // выделено на процесс 512 МБ памяти
        $this->processLimit = 512 * pow(1024, 2);

        // контролируем на половине лимита
        $this->usageLimit = $this->processLimit / 2;

        // Создание директории с логами
        if (!file_exists($this->logPath)) mkdir($this->logPath, 0755, true);
    }

    /**
     * Обрабока очереди
     *
     * @return bool
     */
    abstract protected function run(): bool;

    /**
     * Легкий запуск скрипта
     */
    public function start(): void
    {
        $this->traceLog('');
        $this->traceLog('**************************************************');
        $this->traceLog('[' . date('d.m.Y H:i:s') . '] START daemon');

        try {
            while (true) {
                // Открываем PDO соединение с БД
                $this->db = PDOConnect::connectPDO();

                // Если вернулось false останавливаем демона
                if (!$this->run()) break;

                // Контроль потребляемой памяти скриптом
                $usage_peak = memory_get_peak_usage(true);
                if ($usage_peak >= $this->usageLimit) {
                    // скрипт съедает более половины выделенной памяти
                    $msg = __FILE__ . ' usage: ' . Converter::bytesToMb($usage_peak) . ' MB,'
                        . ' limit: ' . Converter::bytesToMb($this->processLimit) . ' MB';

                    $this->sendSupport($msg, 'script usage a lot of memory!');
                    $this->traceLog($msg);

                    unset($msg);
                }
                unset($usage_peak);

                // Закрываем PDO соединение с БД
                unset($this->db);

                // Для завершения процесса необходимо подложить $stopFile
                if ($this->isStopDaemon()) break;

                sleep($this->sleepTime);
            }
        } catch (\Throwable $e) {
            // Выходим корректно из демона
            $this->sendSupport($e->getMessage(), 'daemon script failed!');
            $this->traceLog('Daemon failed: ' . $e->getMessage());
        }

        // Если был файл остановки удаляем его
        $this->isStopDaemon();

        $this->traceLog('[' . date('d.m.Y H:i:s') . '] STOP daemon');
        $this->traceLog('**************************************************');
    }

    /**
     * Отпарвляем сообщение в саппорт
     *
     * @param $msg
     * @param null|string $subject
     * @return bool
     */
    protected function sendSupport($msg, ?string $subject = null): bool
    {
        if (empty($subject)) $subject = 'cron script msg!';

        return Notify::send($this->emailSupport, $this->scriptName . ' — ' . $subject, $msg);
    }

    /**
     * Пишем лог
     *
     * @param $msg
     */
    protected function traceLog($msg): void
    {
        $log = file_get_contents($this->logPath . DIRECTORY_SEPARATOR . $this->logName);
        $log .= print_r($msg , true) . PHP_EOL;
        file_put_contents($this->logPath . DIRECTORY_SEPARATOR . $this->logName, $log);
    }

    /**
     * Для завершения процесса необходимо подложить $stopFile
     *
     * @return bool
     */
    protected function isStopDaemon(): bool
    {
        if (file_exists($this->stopFile)) {
            unlink($this->stopFile);
            return true;
        }

        return false;
    }
}