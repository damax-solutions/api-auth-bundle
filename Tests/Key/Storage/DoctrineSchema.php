<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Storage;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

class DoctrineSchema
{
    public static function create(): Table
    {
        $table = (new Schema())->createTable('api_key');

        $table
            ->addColumn('key', 'string')
            ->setFixed(true)
            ->setLength(40)
        ;

        $table->addColumn('ttl', 'integer');
        $table->addColumn('identity', 'string');

        return $table;
    }
}
