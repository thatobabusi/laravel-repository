<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination Limit
    |--------------------------------------------------------------------------
    | Default number of results returned per page when using paginate().
    */
    'pagination' => [
        'limit' => env('REPOSITORY_PAGINATION_LIMIT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Criteria Parameters
    |--------------------------------------------------------------------------
    | Customize the query-string parameter names used by RequestCriteria.
    */
    'criteria' => [
        'params' => [
            'search'       => env('REPOSITORY_CRITERIA_SEARCH', 'search'),
            'searchFields' => env('REPOSITORY_CRITERIA_SEARCH_FIELDS', 'searchFields'),
            'filter'       => env('REPOSITORY_CRITERIA_FILTER', 'filter'),
            'orderBy'      => env('REPOSITORY_CRITERIA_ORDER_BY', 'orderBy'),
            'sortedBy'     => env('REPOSITORY_CRITERIA_SORTED_BY', 'sortedBy'),
            'with'         => env('REPOSITORY_CRITERIA_WITH', 'with'),
            'withCount'    => env('REPOSITORY_CRITERIA_WITH_COUNT', 'withCount'),
            'searchJoin'   => env('REPOSITORY_CRITERIA_SEARCH_JOIN', 'searchJoin'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Generator Paths
    |--------------------------------------------------------------------------
    | Configure where generated repository and criteria classes are placed.
    */
    'generator' => [
        'basePath'      => app_path(),
        'rootNamespace' => 'App\\',
        'paths'         => [
            'repositories' => 'Repositories',
            'criteria'     => 'Criteria',
        ],
    ],

];
