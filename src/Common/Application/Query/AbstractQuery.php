<?php

namespace AlephTools\DDD\Common\Application\Query;

use DateTime;
use DateTimeImmutable;
use AlephTools\DDD\Common\Infrastructure\DateHelper;
use AlephTools\DDD\Common\Infrastructure\WeakDto;

/**
 * @property-read string|null $keyword
 * @property-read int|null $limit
 * @property-read int|null $offset
 * @property-read int|null $page
 * @property-read string[]|null $sort
 * @property-read string[]|null $fields
 * @property-read bool $withoutCount
 * @property-read bool $withoutItems
 */
abstract class AbstractQuery extends WeakDto
{
    //region Constants

    public const DEFAULT_PAGE_SIZE = 10;
    public const DEFAULT_PAGE_MAX_SIZE = 1000;

    //endregion

    //region Properties

    protected static $maxPageSize = self::DEFAULT_PAGE_MAX_SIZE;

    protected $keyword;
    protected $limit = self::DEFAULT_PAGE_SIZE;
    protected $offset;
    protected $page;
    protected $sort;
    protected $fields;
    protected $withoutCount = false;
    protected $withoutItems = false;

    //endregion

    public static function getMaxPageSize(): int
    {
        return static::$maxPageSize;
    }

    public static function setMaxPageSize(int $size = self::DEFAULT_PAGE_MAX_SIZE): void
    {
        static::$maxPageSize = $size;
    }

    /**
     * Returns TRUE if the fields is not set or if the given field within $fields array.
     *
     * @param string $field
     * @return bool
     */
    public function containsField(string $field): bool
    {
        return !$this->fields || in_array($field, $this->fields);
    }

    protected function toBoolean($value): bool
    {
        if (is_scalar($value)) {
            $value = strtolower(trim($value));
            return $value === 'true' || $value === '1' || $value === 'on';
        }

        return false;
    }

    protected function toDate($value): ?DateTime
    {
        return DateHelper::parse($value);
    }

    protected function toImmutableDate($value): ?DateTimeImmutable
    {
        return DateHelper::parseImmutable($value);
    }

    //region Setters

    protected function setKeyword($keyword): void
    {
        $this->keyword = is_scalar($keyword) ? (string)$keyword : null;
    }

    protected function setLimit($limit): void
    {
        $this->limit = is_numeric($limit) ? abs((int)$limit) : static::DEFAULT_PAGE_SIZE;

        if ($this->limit > static::$maxPageSize) {
            $this->limit = static::$maxPageSize;
        }
    }

    protected function setOffset($offset): void
    {
        $this->offset = is_numeric($offset) ? abs((int)$offset) : null;
    }

    protected function setPage($page): void
    {
        $this->page = is_numeric($page) ? abs((int)$page) : null;
    }

    protected function setSort($sort): void
    {
        if (!is_string($sort) || $sort === '') {
            return;
        }

        $items = [];
        foreach (explode(',', $sort) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            $first = $item[0];
            if ($first === '-') {
                $items[ltrim(substr($item, 1))] = 'DESC';
            } else if ($first === '+') {
                $items[ltrim(substr($item, 1))] = 'ASC';
            } else {
                $items[$item] = 'ASC';
            }
        }

        $this->sort = $items ?: null;
    }

    protected function setFields($fields): void
    {
        if (is_string($fields) && $fields !== '') {
            $this->fields = [];
            foreach (explode(',', $fields) as $field) {
                $field = trim($field);
                if ($field !== '') {
                    $this->fields[] = $field;
                }
            }
            $this->fields = $this->fields ?: null;
        }
    }

    protected function setWithoutCount($flag): void
    {
        $this->withoutCount = $this->toBoolean($flag);
    }

    protected function setWithoutItems($flag): void
    {
        $this->withoutItems = $this->toBoolean($flag);
    }

    //endregion
}
