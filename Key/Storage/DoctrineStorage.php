<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;

class DoctrineStorage implements Storage
{
    private const FIELD_USERNAME = 'username';
    private const FIELD_EXPIRES = 'expires';

    private $db;
    private $tableName;
    private $fields;

    public function __construct(Connection $db, string $tableName, array $fields = [])
    {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->fields[self::FIELD_USERNAME] = $this->fields[self::FIELD_USERNAME] ?? self::FIELD_USERNAME;
        $this->fields[self::FIELD_EXPIRES] = $this->fields[self::FIELD_EXPIRES] ?? self::FIELD_EXPIRES;
    }

    public function has(string $key): bool
    {
        $sql = sprintf('SELECT 1 FROM %s WHERE id = ?', $this->tableName);

        return (bool) $this->db->executeQuery($sql, [$key], [ParameterType::STRING])->fetchColumn();
    }

    public function remove(string $key): void
    {
        $sql = sprintf('DELETE FROM %s WHERE id = ?', $this->tableName);

        $this->db->executeQuery($sql, [$key], [ParameterType::STRING])->execute();
    }

    public function get(string $key): Key
    {
        $sql = sprintf('SELECT * FROM %s WHERE id = ?', $this->tableName);

        if (false === $row = $this->db->executeQuery($sql, [$key], [ParameterType::STRING])->fetch(FetchMode::ASSOCIATIVE)) {
            throw new KeyNotFoundException();
        }

        return new Key($key, $row[self::FIELD_USERNAME], time() - $row[self::FIELD_EXPIRES]);
    }

    public function add(Key $key): void
    {
        $this->db->insert($this->tableName, [
            'id' => (string) $key,
            self::FIELD_USERNAME => $key->username(),
            self::FIELD_EXPIRES => time() + $key->ttl(),
        ]);
    }
}
