<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Damax\Bundle\ApiAuthBundle\Key\Storage\DoctrineStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFound;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\SqliteSchemaManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group time-sensitive
 */
class DoctrineStorageTest extends TestCase
{
    private const TABLE_NAME = 'api_key';
    private const KEY_TTL = 3600;

    /**
     * @var Connection
     */
    private static $db;

    /**
     * @var DoctrineStorage
     */
    private $storage;

    public static function setUpBeforeClass()
    {
        self::$db = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        (new SqliteSchemaManager(self::$db))->createTable(DoctrineSchema::create());
    }

    public static function tearDownAfterClass()
    {
        self::$db->executeQuery('DROP TABLE ' . self::TABLE_NAME);
        self::$db->close();
    }

    protected function setUp()
    {
        ClockMock::withClockMock(1535726070);

        $this->storage = new DoctrineStorage(self::$db, self::TABLE_NAME);
    }

    /**
     * @test
     */
    public function it_checks_key_in_storage()
    {
        self::$db->insert(self::TABLE_NAME, [
            'key' => 'bar',
            'ttl' => time() + self::KEY_TTL,
            'identity' => 'john.doe@domain.abc',
        ]);
        self::$db->insert(self::TABLE_NAME, [
            'key' => 'baz',
            'ttl' => time() + self::KEY_TTL,
            'identity' => 'jane.doe@domain.abc',
        ]);

        $this->assertFalse($this->storage->has('foo'));
        $this->assertTrue($this->storage->has('bar'));
        $this->assertTrue($this->storage->has('baz'));
    }

    /**
     * @test
     */
    public function it_removes_key_from_storage()
    {
        $this->storage->remove('foo');
        $this->storage->remove('bar');
        $this->storage->remove('baz');

        $this->assertFalse($this->storage->has('bar'));
        $this->assertFalse($this->storage->has('baz'));
    }

    /**
     * @test
     */
    public function it_adds_key_to_storage()
    {
        $key = new Key('foo', 'john.doe@domain.abc', self::KEY_TTL);
        $this->storage->add($key);

        $key = new Key('bar', 'jane.doe@domain.abc', self::KEY_TTL);
        $this->storage->add($key);

        $this->assertTrue($this->storage->has('foo'));
        $this->assertTrue($this->storage->has('bar'));
        $this->assertFalse($this->storage->has('baz'));
    }

    /**
     * @test
     */
    public function it_retrieves_key_from_storage()
    {
        $key = $this->storage->get('foo');

        $this->assertEquals('foo', $key->key());
        $this->assertEquals('john.doe@domain.abc', $key->identity());
        $this->assertEquals(self::KEY_TTL, $key->ttl());

        $key = $this->storage->get('bar');

        $this->assertEquals('bar', $key->key());
        $this->assertEquals('jane.doe@domain.abc', $key->identity());
        $this->assertEquals(self::KEY_TTL, $key->ttl());
    }

    /**
     * @test
     */
    public function it_fails_retrieving_missing_key()
    {
        $this->expectException(KeyNotFound::class);

        $this->storage->get('baz');
    }
}
