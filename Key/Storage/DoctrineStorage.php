<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class DoctrineStorage implements Storage
{
    private const FIELD_KEY = 'key';
    private const FIELD_IDENTITY = 'identity';
    private const FIELD_EXPIRES = 'expires';

    private $db;
    private $tableName;
    private $fields = [
        self::FIELD_KEY => self::FIELD_KEY,
        self::FIELD_IDENTITY => self::FIELD_IDENTITY,
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

        return (bool) $this->db->executeQuery($sql, [$key])->fetchColumn();
    }

    public function remove(string $key): void
    {
        $sql = sprintf('DELETE FROM %s WHERE %s = ?', $this->tableName, $this->fields[self::FIELD_KEY]);

        $this->db->executeQuery($sql, [$key])->execute();
    }

    public function add(Key $key): void
    {
        $this->db->insert($this->tableName, [
            $this->fields[self::FIELD_KEY] => (string) $key,
            $this->fields[self::FIELD_IDENTITY] => $key->identity(),
            $this->fields[self::FIELD_EXPIRES] => time() + $key->ttl(),
        ]);
    }

    public function get(string $key): Key
    {
        $fields = $this->fields[self::FIELD_IDENTITY] . ', ' . $this->fields[self::FIELD_EXPIRES];

        $sql = sprintf('SELECT %s FROM %s WHERE %s = ?', $fields, $this->tableName, $this->fields[self::FIELD_KEY]);

        if (false === $row = $this->db->executeQuery($sql, [$key])->fetch(FetchMode::ASSOCIATIVE)) {
            throw new KeyNotFoundException();
        }

        return new Key($key, $row[$this->fields[self::FIELD_IDENTITY]], $row[$this->fields[self::FIELD_EXPIRES]] - time());
    }
}
