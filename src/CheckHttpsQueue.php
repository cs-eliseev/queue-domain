<?php

namespace queue;

require_once __DIR__ . '/../vendor/autoload.php';

use queue\Abstracts\AbstractQueue;
use queue\Helpers\DomainConnect;
use queue\Helpers\Notify;
use queue\Info\Channel;
use queue\Info\Stat;

class CheckHttpsQueue extends AbstractQueue
{
    const TABLE_NAME = 'queue_domain';

    const SERVICE_HTTP = 'http';
    const SERVICE_HTTPS = 'https';

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $mailTmplName = 'mail';

    public function run(): bool
    {

        $list = $this->db->select('SELECT * FROM queue_domain WHERE stat_id = ?', [Stat::getStatIndexByName(Stat::STATUS_WAITING)]);

        // Обрабатываем очередь если данные есть
        if ($this->db->isExistLastQuery()) {
            foreach ($list as $task) {

                $this->id = (int)$task['id'];

                // Проверяем что текущая задача еще в ожидании
                if (!$this->db->exist(self::TABLE_NAME, ['id' => $this->id, 'stat_id' => Stat::getStatIndexByName(Stat::STATUS_WAITING)])) continue;

                try {
                    $this->traceLog('---------------------- ');
                    $this->traceLog('[' . $this->id . '] Start process queue id: ' . $this->id);
                    $this->db->update(self::TABLE_NAME, [
                        'stat_id' => Stat::getStatIndexByName(Stat::STATUS_PROCESS)
                    ], [
                        'id' => $this->id
                    ]);

                    // Get information channel
                    $emails = $this->db->selectColumn('notify_settings', 'email', [
                        'user_id' => $task['user_id'],
                        'channel_id' => Channel::getChannelIdByName(Channel::CHANNEL_INFO)
                    ]);

                    // Set error queue - information channel undefined
                    if (!$this->db->isExistLastQuery()) {
                        $msg = 'Information channel undefined!';

                        $this->db->update(self::TABLE_NAME, [
                            'stat_id' => Stat::getStatIndexByName(Stat::STATUS_ERROR),
                            'error_log' => $msg
                        ], [
                            'id' => $this->id
                        ]);

                        $this->traceLog('[' . $this->id . '] ' . $msg);
                        $this->traceLog('[' . $this->id . '] Stop process queue id: ' . $this->id);
                        unset($msg);
                        continue;
                    }

                    // Get login
                    $login = $this->db->selectCell('users', 'login', ['id' => $task['user_id']]);
                    $this->traceLog('[' . $this->id . '] Process get login: ' . $login);

                    // Get domain
                    $domain = $this->db->selectCell('domains', 'domain', ['id' => $task['domain_id']]);
                    $this->traceLog('[' . $this->id . '] Process get domain: ' . $domain);

                    // Check user domain
                    $is_redirect = $this->isRedirectToHttps($domain);
                    $this->traceLog('[' . $this->id . '] Domain ' . $domain . ' redirect is ' . ($is_redirect ? '' : 'not ') . 'configured!');

                    // Get mail content
                    $mailContent = Notify::setParamsMailByTmplName($this->mailTmplName, [
                            '/%login%/' => $login,
                            '/%domain%/' => $domain,
                            '/%status%/' => $is_redirect ? 'подтвержден' : 'не подтвержден'
                    ]);

                    // Send user mail
                    Notify::send(
                        implode(', ', $emails),
                        $login . ': Проверка переадресации вашего домена',
                        $mailContent
                    );
                    $this->traceLog('[' . $this->id . '] Send mail info');

                    $this->db->update(self::TABLE_NAME, [
                        'stat_id' => Stat::getStatIndexByName(Stat::STATUS_PROCESS)
                    ], [
                        'id' => $this->id
                    ]);

                } catch (\Throwable $e) {
                    // Set error queue
                    $this->db->update(self::TABLE_NAME, [
                        'stat_id' => Stat::getStatIndexByName(Stat::STATUS_ERROR),
                        'error_log' => $e->getMessage()
                    ], [
                        'id' => $this->id
                    ]);
                    $this->traceLog('[' . $this->id . '] Queue process error: ' . $e->getMessage());
                    $this->sendSupport($e->getMessage(), 'error queue id: ' . $this->id);
                }

                $this->traceLog('[' . $this->id . '] Stop process queue id: ' . $this->id);
            }
        }

        return true;
    }

    /**
     * @param string $domain
     * @return bool
     */
    protected function isRedirectToHttps(string $domain): bool
    {
        $ch = DomainConnect::connectDomain(self::SERVICE_HTTP . '://' . $domain);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $end_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        return in_array($http_code, [301, 302]) && preg_match('/^' . self::SERVICE_HTTPS . ':\/\/.*/i', $end_url) === 1;
    }
}

try {
    $cronScan = new CheckHttpsQueue();
    $cronScan->start();
} catch (\Throwable $e) {
    Notify::send(
        'a.k.eliseev@gmail.com',
        'CheckSslQueue — cron script failed!',
        $e->getMessage()
    );
}
