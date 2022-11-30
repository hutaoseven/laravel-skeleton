<?php

namespace App\Macros\QueryBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Relations\Relation
 */
class WhereStartsWithQueryBuilderMacro
{
    public function whereStartsWith(): callable
    {
        return function ($column, string $value, string $boolean = 'and', bool $not = false) {
            $operator = $not ? 'not like' : 'like';

            return $this->where($column, $operator, "$value%", $boolean);
        };
    }

    public function whereNotStartsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereStartsWith($column, $value, 'and', true);
        };
    }

    public function orWhereStartsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereStartsWith($column, $value, 'or');
        };
    }

    public function orWhereNotStartsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereStartsWith($column, $value, 'or', true);
        };
    }
}
