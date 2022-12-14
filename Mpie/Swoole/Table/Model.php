<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Swoole\Table;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Mpie\Swoole\Table\Exception\DuplicateKeyException;
use Mpie\Swoole\Table\Exception\ModelNotFoundException;
use Mpie\Utils\Collection;
use Mpie\Utils\Contract\Arrayable;
use Mpie\Utils\Contract\Jsonable;
use Swoole\Table;

abstract class Model implements ArrayAccess, Arrayable, JsonSerializable, Jsonable
{
    /** @var string Table Name */
    protected static string $table;

    /** @var int|string Primary Key */
    protected string|int $key;

    protected static array $fillable = [];

    protected static array $casts = [];

    protected array $attributes = [];

    /**
     * @param int|string $key Primary Key
     */
    public function __construct(int|string $key, array $attributes = [])
    {
        $this->key = $key;
        if (! empty($attributes)) {
            $this->fill($attributes);
        }
    }

    /**
     * @param $name
     *
     * @return null|mixed
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (in_array($name, static::$fillable)) {
            if (isset(static::$casts[$name])) {
                switch (static::$casts[$name]) {
                    case 'integer':
                    case 'int':
                        $value = (int) $value;
                        break;
                }
            }
            $this->attributes[$name] = $value;
        }
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function getPrimaryKey(): string
    {
        return $this->key;
    }

    /**
     * @return $this
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }

    public function increment(string $column, int $step = 1): int
    {
        $current         = (int) $this->{$column} + $step;
        $this->{$column} = $current;
        $this->save();
        return $current;
    }

    public function decrement(string $column, int $step = 1): int
    {
        return $this->increment($column, -$step);
    }

    /**
     * @param $value
     */
    public static function findWhere(string $field, $value): Collection
    {
        if (! in_array($field, static::$fillable)) {
            throw new InvalidArgumentException('??????????????????');
        }
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            if (
                ($value instanceof Closure && $value($field, $item[$field], $item))
                || $item[$field] === $value
            ) {
                $collection->push(new static($key, $item));
            }
        }
        return $collection;
    }

    /**
     * Retrieved by array condition.
     */
    public static function findByFields(array $fields): Collection
    {
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            foreach ($fields as $k => $v) {
                if (! $item[$k] == $v) {
                    continue 2;
                }
            }
            $collection->push(new static($key, $item));
        }
        return $collection;
    }

    /**
     * In query.
     */
    public static function findWhereIn(string $field, array $in): Collection
    {
        return static::findWhere($field, function ($k, $v, &$item) use (&$in) {
            return in_array($v, $in);
        });
    }

    public static function find(string $key): ?static
    {
        return static::getSwooleTable()->exists($key)
            ? new static($key, static::getSwooleTable()->get($key))
            : null;
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function findOrFail(string $key): static
    {
        return static::find($key) ?: throw new ModelNotFoundException('???????????????');
    }

    public function save(): bool
    {
        return static::getSwooleTable()->set($this->key, $this->attributes);
    }

    /**
     * delete.
     */
    public function delete()
    {
        static::destroy($this->getPrimaryKey());
    }

    /**
     * @return static
     * @throws DuplicateKeyException
     */
    public static function create(string $key, array $attributes = []): Model
    {
        if (static::exists($key)) {
            throw new DuplicateKeyException('Duplicate key: ' . $key);
        }
        $model = new static($key, $attributes);
        $model->save();
        return $model;
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function update(string $key, array $attributes = []): static
    {
        $model = static::findOrFail($key);
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    /**
     * @return $this
     */
    public function replace(array $attributes): static
    {
        $this->fill($attributes)->save();
        return $this;
    }

    public static function exists(string $key): bool
    {
        return static::getSwooleTable()->exists($key);
    }

    public static function destroy(string $key): bool
    {
        return static::getSwooleTable()->del($key);
    }

    public static function count(): int
    {
        return static::getSwooleTable()->count();
    }

    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (in_array($offset, static::$fillable)) {
            static::$fillable[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->attributes[$offset]);
        }
    }

    public static function all(): Collection
    {
        $collection = Collection::make();
        foreach (static::getSwooleTable() as $key => $item) {
            $collection->push(new static($key, $item));
        }
        return $collection;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toJson(int $options = 256): string
    {
        return (string) json_encode($this->toArray(), $options);
    }

    /**
     * Get table.
     */
    protected static function getSwooleTable(): Table
    {
        return Manager::get(static::$table);
    }
}
