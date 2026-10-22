<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Abstract OOP Model
 * Provides clean CRUD operations and data mapping for models.
 */
abstract class Model
{
    /**
     * Database table name associated with the model
     */
    protected static string $table = '';

    /**
     * Primary key column
     */
    protected static string $primaryKey = 'id';

    /**
     * Model attribute storage
     */
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Fetch all records as model instances
     *
     * @return static[]
     */
    public static function all(string $orderBy = 'id DESC'): array
    {
        $table = static::$table;
        $sql = "SELECT * FROM `{$table}` ORDER BY {$orderBy}";
        $rows = Database::fetchAll($sql);

        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Find a record by primary key
     *
     * @return static|null
     */
    public static function find(int $id): ?static
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $sql = "SELECT * FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1";
        $row = Database::fetchOne($sql, ['id' => $id]);

        return $row ? new static($row) : null;
    }

    /**
     * Find a record by primary key (alias of find)
     *
     * @return static|null
     */
    public static function findById(int $id): ?static
    {
        return static::find($id);
    }

    /**
     * Find records matching a condition
     *
     * @return static[]
     */
    public static function where(string $column, mixed $value, string $operator = '='): array
    {
        $table = static::$table;
        $sql = "SELECT * FROM `{$table}` WHERE `{$column}` {$operator} :val";
        $rows = Database::fetchAll($sql, ['val' => $value]);

        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Find first record matching condition
     *
     * @return static|null
     */
    public static function findWhere(string $column, mixed $value): ?static
    {
        $results = static::where($column, $value);
        return $results[0] ?? null;
    }

    /**
     * Count all rows in table
     */
    public static function count(): int
    {
        $table = static::$table;
        return (int)Database::fetchValue("SELECT COUNT(*) FROM `{$table}`");
    }

    /**
     * Create a new record in table
     */
    public static function create(array $data): static
    {
        $table = static::$table;
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $table,
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        Database::query($sql, $data);
        $id = (int)Database::lastInsertId();

        return static::find($id) ?? new static(array_merge($data, [static::$primaryKey => $id]));
    }

    /**
     * Update an existing record
     */
    public static function update(int $id, array $data): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;

        $fields = [];
        $params = ['_id' => $id];
        foreach ($data as $col => $val) {
            $fields[] = "`{$col}` = :{$col}";
            $params[$col] = $val;
        }

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = :_id",
            $table,
            implode(', ', $fields),
            $pk
        );

        $stmt = Database::query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete record by ID
     */
    public static function delete(int $id): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $sql = "DELETE FROM `{$table}` WHERE `{$pk}` = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
