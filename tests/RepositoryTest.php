<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Tests;

use Mpie\Config\Repository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RepositoryTest extends TestCase
{
    protected Repository $repository;

    protected function setUp(): void
    {
        $this->tearDown();
    }

    protected function tearDown(): void
    {
        $this->repository = new Repository();
    }

    public function testAll()
    {
        $this->assertEquals($this->repository->all(), []);
    }

    public function testLoadOne()
    {
        $this->repository->loadOne(__DIR__ . '/examples/app.php');
        $this->assertArrayHasKey('app', $this->repository->all());
    }

    public function testLoad()
    {
        $this->repository->load([__DIR__ . '/examples/app.php', __DIR__ . '/examples/db.php']);
        $this->assertArrayHasKey('app', $this->repository->all());
        $this->assertArrayHasKey('db', $this->repository->all());
    }

    public function testGet()
    {
        $this->repository->loadOne(__DIR__ . '/examples/app.php');
        $this->assertEquals($this->repository->get('app.id'), 123);
        $this->assertNull($this->repository->get('app.none'));
    }

    public function testSet()
    {
        $this->repository->set(__METHOD__, __METHOD__);
        $this->assertEquals($this->repository->get(__METHOD__), __METHOD__);
    }
}
