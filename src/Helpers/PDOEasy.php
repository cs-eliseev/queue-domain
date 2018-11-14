<?php

namespace queue\Helpers;

class PDOEasy extends \PDO
{
    /**
     * @var int
     */
    protected $queryRow = 0;

    /**
     * @var bool
     */
    protected $isExistQuery = false;

    /**
     * @var null|mixed
     */
    protected $queryData = null;

    /**
     * Простое выполнение запроса
     *
     * @param string $query
     * @param array $param
     * @return array|null
     */
    public function select(string $query, array $param = []): ?array
    {
        // Сбрасываем последние данные
        $this->resetData();

        // Записываем запрос
        $stmt = $this->prepare($query);

        // Подстваляем параметры
        $stmt->execute($param);

        // Устанавливаем значения
        $this->queryRow = $stmt->rowCount();
        $this->isExistQuery = (bool)$this->queryRow;
        $this->queryData = $this->isExistQuery ? $stmt->fetchAll() : null;

        unset($stmt);

        return $this->queryData;
    }

    /**
     * @param string $table
     * @param string $column
     * @param array $where
     * @return null|string
     */
    public function selectCell(string $table, string $column, array $where = []): ?string
    {
        // Сбрасываем последние данные
        $this->resetData();

        // Формируем запрос
        $query = "SELECT {$column} FROM {$table}";
        if (!empty($where)) {
            $columns_where = array_keys($where);
            foreach ($columns_where AS &$el) $el = '"' . $el . '" = ?';
            $query.= ' WHERE ' . implode(' AND ', $columns_where);
        }
        $query.= ' LIMIT 1';

        // Записываем запрос
        $stmt = $this->prepare($query);

        // Подстваляем параметры
        $stmt->execute(empty($where) ? [] : array_values($where));

        // Устанавливаем значения
        $this->queryRow = $stmt->rowCount();
        $this->isExistQuery = (bool)$this->queryRow;
        $this->queryData = $this->isExistQuery ? $stmt->fetchColumn() : null;

        unset($stmt);

        return $this->queryData;
    }

    /**
     * @param string $table
     * @param string $column
     * @param array $where
     * @return array|null
     */
    public function selectColumn(string $table, string $column, array $where = []): ?array
    {
        // Сбрасываем последние данные
        $this->resetData();

        // Формируем запрос
        $query = "SELECT {$column} FROM {$table}";
        if (!empty($where)) {
            $columns_where = array_keys($where);
            foreach ($columns_where AS &$el) $el = '"' . $el . '" = ?';
            $query.= ' WHERE ' . implode(' AND ', $columns_where);
        }

        // Записываем запрос
        $stmt = $this->prepare($query);

        // Подстваляем параметры
        $stmt->execute(empty($where) ? [] : array_values($where));

        // Устанавливаем значения
        $this->queryRow = $stmt->rowCount();
        $this->isExistQuery = (bool)$this->queryRow;
        $this->queryData = $this->isExistQuery ? $stmt->fetchAll(\PDO::FETCH_COLUMN, 0) : null;

        unset($stmt);

        return $this->queryData;
    }

    /**
     * Проверка существованя данных
     *
     * @param string $table
     * @param array $where
     * @return bool
     */
    public function exist(string $table, array $where): bool
    {
        // Сбрасываем последние данные
        $this->resetData();

        // Формируем запрос
        $query = 'SELECT COUNT(*) FROM ' . $table;
        $columns_where = array_keys($where);
        foreach ($columns_where AS &$el) $el = '"' . $el . '" = ?';
        $query.= ' WHERE ' . implode(' AND ', $columns_where);
        $query.= ' LIMIT 1';

        // Записываем запрос
        $stmt = $this->prepare($query);

        // Подстваляем параметры
        $stmt->execute(array_values($where));

        // Устанавливаем значения
        $this->queryRow = $stmt->fetchColumn();
        $this->isExistQuery = (bool)$this->queryRow;
        $this->queryData = $this->queryRow;

        return $this->isExistQuery;
    }

    public function update(string $table, array $set, array $where = [])
    {
        // Сбрасываем последние данные
        $this->resetData();

        // Преобразуем значения
        $columns_set = array_keys($set);
        foreach ($columns_set AS &$el) $el = '"' . $el . '" = ?';

        // Преобразуем условия
        $columns_where = array_keys($where);
        foreach ($columns_where AS &$el) $el = '"' . $el . '" = ?';

        // Формируем запрос
        $stmt = $this->prepare('UPDATE ' . $table . ' SET ' . implode(', ', $columns_set)
                               . ' WHERE ' . implode(' AND ', $columns_where));

        // Формируем параметры запроса
        $k = 1;
        foreach (array_values($set) as $v) {
            $type = is_null($v) ? \PDO::PARAM_NULL : \PDO::PARAM_STR;
            $stmt->bindValue($k++, $v, $type);
        }
        foreach (array_values($where) as $v) {
            $stmt->bindValue($k++, $v);
        }

        // Устанавливаем значения
        $this->queryRow = $stmt->rowCount();
        $this->isExistQuery = $stmt->execute();
        $this->queryData = $stmt->execute();

        return $this->queryRow;
    }

    /**
     * Получаем количество строк последнего запроса
     *
     * @return int
     */
    public function getLastQueryRow(): int
    {
        return $this->queryRow;
    }

    /**
     * Получаем данные из последнего запроса
     *
     * @return mixed|null
     */
    public function getLastQueryData()
    {
        return $this->queryData;
    }

    /**
     * Существуют ли данные в последнем запросем
     *
     * @return bool
     */
    public function isExistLastQuery(): bool
    {
        return $this->isExistQuery;
    }

    /**
     * Сброс данных на дефолтные
     */
    protected function resetData(): void
    {
        $this->queryRow = 0;
        $this->isExistQuery = false;
        $this->queryData = null;
    }

    /**
     * Открываем PDO соединение
     *
     * @param array $connectSettings
     * @return PDOEasy
     */
    public static function connectPDO(array $connectSettings): PDOEasy
    {
        $connectSettings = array_merge([
            'db_type' => 'mysql',
            'db_host' => 'localhost',
            'db_user' => 'root',
            'db_pass' => ''
        ], $connectSettings);

        return new self(self::getDSN($connectSettings), $connectSettings['db_user'], $connectSettings['db_pass']);
    }

    /**
     * Получаем DSN данные для PDO подключения
     *
     * @param array $settings
     * @return string
     */
    protected static function getDSN(array $settings): string
    {
        if (empty($settings['db_name'])) throw new \PDOException('DB name unknown');

        return $settings['db_type'] . ':host=' . $settings['db_host']
             . (empty($settings['db_port']) ? '' : ';port=' . $settings['db_port'])
             . ';dbname=' . $settings['db_name'];
    }
}