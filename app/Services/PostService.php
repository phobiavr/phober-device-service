<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;

class PostService {
    public function paginate(PageableRequest $request): LengthAwarePaginator {
        return Post::paginateFromRequest($request);
    }
}
