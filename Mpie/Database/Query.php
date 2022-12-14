<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Database;

use Closure;
use Mpie\Database\Contract\ConnectorInterface;
use Mpie\Database\Contract\QueryInterface;
use Mpie\Database\Event\QueryExecuted;
use Mpie\Database\Query\Builder;
use PDO;
use PDOException;
use PDOStatement;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Database\PDOProxy;
use Throwable;

class Query implements QueryInterface
{
    /**
     * @var PDO|PDOProxy
     */
    protected mixed $connection;

    public function __construct(
        protected ConnectorInterface $connector,
        protected ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->connection = $this->connector->get();
    }

    /**
     * @return false|PDOStatement
     */
    public function statement(string $query, array $bindings = [])
    {
        try {
            $executedAt   = microtime(true);
            $PDOStatement = $this->connection->prepare($query);
            foreach ($bindings as $key => $value) {
                $PDOStatement->bindValue(is_string($key) ? $key : $key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $PDOStatement->execute();
            $this->eventDispatcher?->dispatch(new QueryExecuted($query, $bindings, $executedAt));
            return $PDOStatement;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . sprintf(' (SQL: %s)', $query), (int) $e->getCode(), $e->getPrevious());
        }
    }

    public function getPDO(): PDO
    {
        return $this->connection;
    }

    public function select(string $query, array $bindings = [], int $mode = PDO::FETCH_ASSOC, ...$args): bool|array
    {
        return $this->statement($query, $bindings)->fetchAll($mode, ...$args);
    }

    public function selectOne(string $query, array $bindings = [], int $mode = PDO::FETCH_ASSOC, ...$args): mixed
    {
        return $this->statement($query, $bindings)->fetch($mode, ...$args);
    }

    public function table(string $table, ?string $alias = null): Builder
    {
        return (new Builder($this))->from($table, $alias);
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->statement($query, $bindings)->rowCount();
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->statement($query, $bindings)->rowCount();
    }

    public function insert(string $query, array $bindings = [], ?string $id = null): bool|string
    {
        $this->statement($query, $bindings);
        return $this->getPDO()->lastInsertId($id);
    }

    public function begin(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * @throws Throwable
     */
    public function transaction(Closure $transaction): mixed
    {
        $this->begin();
        try {
            $result = ($transaction)($this);
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }
}
