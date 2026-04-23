<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Http\Requests\Admin\AdminListRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

trait ListsAdminResources
{
    protected function paginateAdminList(
        EloquentBuilder|QueryBuilder $query,
        AdminListRequest $request,
        array $allowedSorts,
        string $defaultSort = 'created_at',
        string $defaultDirection = 'desc',
        int $defaultPerPage = 25,
    ): LengthAwarePaginator {
        $sort = in_array($request->validated('sort'), $allowedSorts, true)
            ? $request->validated('sort')
            : $defaultSort;
        $dir = ($request->validated('dir') ?? $defaultDirection) === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($request->validated('per_page') ?? $defaultPerPage);

        return $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();
    }
}
