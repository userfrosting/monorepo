<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;

/**
 * UserFrosting's custom Query Builder Class.
 */
class Builder extends QueryBuilder
{
    /**
     * @var string[]|null List of excluded columns
     */
    protected ?array $excludedColumns = null;

    /**
     * Perform a "begins with" pattern match on a specified column in a query.
     *
     * @param string $field The column to match
     * @param string $value The value to match
     *
     * @return static
     */
    public function beginsWith(string $field, string $value): static
    {
        return $this->where($field, 'LIKE', "$value%");
    }

    /**
     * Perform an "ends with" pattern match on a specified column in a query.
     *
     * @param string $field The column to match
     * @param string $value The value to match
     *
     * @return static
     */
    public function endsWith(string $field, string $value): static
    {
        return $this->where($field, 'LIKE', "%$value");
    }

    /**
     * Add columns to be excluded from the query.
     *
     * @param string[]|string $column The column(s) to exclude
     *
     * @return static
     */
    public function exclude(array|string $column): static
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->excludedColumns = array_merge((array) $this->excludedColumns, $column);

        return $this;
    }

    /**
     * Perform a pattern match on a specified column in a query.
     *
     * @param string $field The column to match
     * @param string $value The value to match
     *
     * @return static
     */
    public function like(string $field, string $value): static
    {
        return $this->where($field, 'LIKE', "%$value%");
    }

    /**
     * Perform a pattern match on a specified column in a query.
     *
     * @param string $field The column to match
     * @param string $value The value to match
     *
     * @return static
     */
    public function orLike(string $field, string $value): static
    {
        return $this->orWhere($field, 'LIKE', "%$value%");
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param string[]|string $columns
     *
     * @return \Illuminate\Support\Collection<int, \UserFrosting\Sprinkle\Core\Database\Models\Model>
     */
    public function get($columns = ['*'])
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = Arr::wrap($columns);
        }

        // Exclude any explicitly excluded columns
        if (!is_null($this->excludedColumns)) {
            $this->removeExcludedSelectColumns();
        }

        $results = $this->processor->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return collect($results);
    }

    /**
     * Remove excluded columns from the select column list.
     */
    protected function removeExcludedSelectColumns(): void
    {
        // Convert current column list and excluded column list to fully-qualified list
        $this->columns = $this->convertColumnsToFullyQualified($this->columns);
        $excludedColumns = $this->convertColumnsToFullyQualified($this->excludedColumns);

        // Remove any explicitly referenced excludable columns
        $this->columns = array_diff($this->columns, $excludedColumns);

        // Replace any remaining wildcard columns (*, table.*, etc) with a list
        // of fully-qualified column names
        $this->columns = $this->replaceWildcardColumns($this->columns);

        $this->columns = array_diff($this->columns, $excludedColumns);
    }

    /**
     * Find any wildcard columns ('*'), remove it from the column list and replace with an explicit list of columns.
     *
     * @param string[] $columns
     *
     * @return string[]
     */
    protected function replaceWildcardColumns(array $columns): array
    {
        $wildcardTables = $this->findWildcardTables($columns);

        foreach ($wildcardTables as $wildColumn => $table) {
            $schemaColumns = $this->getQualifiedColumnNames($table);

            // Remove the `*` or `.*` column and replace with the individual schema columns
            $columns = array_diff($columns, [$wildColumn]);
            $columns = array_merge($columns, $schemaColumns);
        }

        return $columns;
    }

    /**
     * Return a list of wildcard columns from the list of columns, mapping columns to their corresponding tables.
     *
     * @param string[] $columns
     *
     * @return string[]
     */
    protected function findWildcardTables(array $columns): array
    {
        $tables = [];

        foreach ($columns as $column) {
            if (substr($column, -1) === '*') {
                $tableName = explode('.', $column)[0];
                if ($tableName !== '') {
                    $tables[$column] = $tableName;
                }
            }
        }

        return $tables;
    }

    /**
     * Gets the fully qualified column names for a specified table.
     *
     * @param string|null $table
     *
     * @return string[]
     */
    protected function getQualifiedColumnNames(?string $table = null): array
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        return $this->convertColumnsToFullyQualified($schema->getColumnListing($table), $table);
    }

    /**
     * Fully qualify any unqualified columns in a list with this builder's table name.
     *
     * @param string[]    $columns
     * @param string|null $table
     *
     * @return string[]
     */
    protected function convertColumnsToFullyQualified(array $columns, ?string $table = null): array
    {
        if (is_null($table)) {
            $table = $this->from;
            $table = ($table instanceof Expression) ? $table->getValue($this->grammar) : $table;
        }

        array_walk($columns, function (&$item, $key) use ($table) {
            if (strpos($item, '.') === false) {
                $item = "$table.$item";
            }
        });

        return $columns;
    }
}
