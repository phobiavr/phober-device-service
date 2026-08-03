<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Phobiavr\PhoberLaravelCommon\Pageable\PageableRequest;

class PostService {
    /** @return LengthAwarePaginator<int, Post> */
    public function paginate(PageableRequest $request): LengthAwarePaginator {
        return Post::paginateFromRequest($request);
    }
}
