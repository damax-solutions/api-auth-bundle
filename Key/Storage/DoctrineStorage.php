<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;

final class DoctrineStorage implements Storage
{
    private const FIELD_KEY = 'key';
    private const FIELD_USERNAME = 'username';
    private const FIELD_EXPIRES = 'expires';

    private $db;
    private $tableName;
    private $fields = [
        self::FIELD_KEY => self::FIELD_KEY,
        self::FIELD_USERNAME => self::FIELD_USERNAME,
        self::FIELD_EXPIRES => self::FIELD_EXPIRES,
    ];

    public function __construct(Connection $db, string $tableName, array $fields = [])
    {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->fields = array_replace($this->fields, $fields);
    }

    public function has(string $key): bool
    {
        $sql = sprintf('SELECT 1 FROM %s WHERE %s = ?', $this->tableName, $this->fields[self::FIELD_KEY]);

        return (bool) $this->db->executeQuery($sql, [$key], [ParameterType::STRING])->fetchColumn();
    }

    public function remove(string $key): void
    {
        $sql = sprintf('DELETE FROM %s WHERE %s = ?', $this->tableName, $this->fields[self::FIELD_KEY]);

        $this->db->executeQuery($sql, [$key], [ParameterType::STRING])->execute();
    }

    public function add(Key $key): void
    {
        $this->db->insert($this->tableName, [
            $this->fields[self::FIELD_KEY] => (string) $key,
            $this->fields[self::FIELD_USERNAME] => $key->username(),
            $this->fields[self::FIELD_EXPIRES] => time() + $key->ttl(),
        ]);
    }

    public function get(string $key): Key
    {
        $sql = sprintf('SELECT * FROM %s WHERE %s = ?', $this->tableName, $this->fields[self::FIELD_KEY]);

        if (false === $row = $this->db->executeQuery($sql, [$key], [ParameterType::STRING])->fetch(FetchMode::ASSOCIATIVE)) {
            throw new KeyNotFoundException();
        }

        return new Key($key, $row[$this->fields[self::FIELD_USERNAME]], time() - $row[$this->fields[self::FIELD_EXPIRES]]);
    }
}
