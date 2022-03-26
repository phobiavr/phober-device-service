<?php

namespace App\Models;

use App\Http\Requests\Pageable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait WithPagination {
  public static function paginate(Pageable $request): LengthAwarePaginator {
    extract($request->pagination());

    return self::query()->paginate($perPage, $columns, $pageName, $page);
  }
}