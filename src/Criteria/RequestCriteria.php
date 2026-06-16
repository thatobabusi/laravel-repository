<?php

namespace Laravel\Repository\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Repository\Contracts\CriteriaInterface;
use Laravel\Repository\Contracts\RepositoryInterface;

class RequestCriteria implements CriteriaInterface
{
    public function __construct(protected Request $request) {}

    public function apply(mixed $model, RepositoryInterface $repository): mixed
    {
        $fieldSearchable = $repository->getFieldsSearchable();

        $search      = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter      = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy     = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy    = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $with        = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $withCount   = $this->request->get(config('repository.criteria.params.withCount', 'withCount'), null);
        $searchJoin  = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);

        $sortedBy = $sortedBy === 'desc' ? 'desc' : 'asc';

        if ($search !== null && $fieldSearchable) {
            $searchFields = is_array($searchFields) ? $searchFields : $this->parserSearchData($searchFields ?? '');
            $searchData   = $this->parserSearchData($search);
            $searchValue  = $this->parserSearchValue($search);
            $isFirstField = true;
            $modelForceAndWhere = strtolower($searchJoin ?? '') === 'and';

            $fields = $this->parserFieldsSearch($fieldSearchable, $searchFields);

            $model = $model->where(function (Builder $query) use (
                $fields, $searchData, $searchValue, $modelForceAndWhere, &$isFirstField
            ) {
                foreach ($fields as $field => $condition) {
                    $value = ($searchData[$field] ?? $searchValue);

                    if ($value === null) {
                        continue;
                    }

                    if ($condition === 'like') {
                        $value = "%{$value}%";
                    } elseif ($condition === 'ilike') {
                        $value = "%{$value}%";
                    }

                    $orWhere = ($isFirstField || $modelForceAndWhere) ? 'where' : 'orWhere';

                    if ($condition === 'in') {
                        $values = is_array($value) ? $value : explode(',', $value);
                        $query->{$orWhere . 'In'}($field, $values);
                    } elseif ($condition === 'between') {
                        $values = is_array($value) ? $value : explode(',', $value);
                        $query->{$orWhere . 'Between'}($field, $values);
                    } else {
                        $query->{$orWhere}($field, $condition, $value);
                    }

                    $isFirstField = false;
                }
            });
        }

        if ($orderBy !== null) {
            $orderBySplit = explode(';', $orderBy);

            foreach ($orderBySplit as $orderByPart) {
                [$column] = explode('|', $orderByPart);
                $model = $model->orderBy(trim($column), $sortedBy);
            }
        }

        if ($filter !== null) {
            $filter = is_array($filter) ? $filter : explode(';', $filter);
            $model  = $model->select($filter);
        }

        if ($with !== null) {
            $with  = is_array($with) ? $with : explode(';', $with);
            $model = $model->with($with);
        }

        if ($withCount !== null) {
            $withCount = is_array($withCount) ? $withCount : explode(';', $withCount);
            $model     = $model->withCount($withCount);
        }

        return $model;
    }

    protected function parserSearchData(string $search): array
    {
        $searchData = [];

        if (str_contains($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                if (str_contains($row, ':')) {
                    [$field, $value] = explode(':', $row, 2);
                    $searchData[trim($field)] = trim($value);
                }
            }
        }

        return $searchData;
    }

    protected function parserSearchValue(string $search): ?string
    {
        if (str_contains($search, ';') || str_contains($search, ':')) {
            $values = explode(';', $search);

            foreach ($values as $value) {
                $s = explode(':', $value);

                if (count($s) === 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }

    protected function parserFieldsSearch(array $fields, array $searchFields): array
    {
        $parsed = [];

        foreach ($fields as $field => $condition) {
            if (is_numeric($field)) {
                $field     = $condition;
                $condition = '=';
            }

            if (isset($searchFields[$field])) {
                $condition = $searchFields[$field];
            }

            $parsed[$field] = $condition;
        }

        if ($searchFields) {
            $parsed = array_intersect_key($parsed, $searchFields + array_flip(array_keys($parsed)));

            $filteredBySearchFields = [];
            foreach ($searchFields as $requestedField => $requestedCondition) {
                $key = is_numeric($requestedField) ? $requestedCondition : $requestedField;
                if (isset($parsed[$key])) {
                    $filteredBySearchFields[$key] = is_numeric($requestedField)
                        ? $parsed[$key]
                        : $requestedCondition;
                }
            }

            if ($filteredBySearchFields) {
                $parsed = $filteredBySearchFields;
            }
        }

        return $parsed;
    }
}
