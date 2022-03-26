<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use JsonSerializable;

class GameCollection extends ResourceCollection {
  /**
   * Transform the resource collection into an array.
   *
   * @param Request $request
   * @return array|Arrayable|JsonSerializable
   */
  public function toArray($request): array|JsonSerializable|Arrayable {
    /** @var GameCollection|LengthAwarePaginator $this */

    return [
      'data' => $this->collection,
      'total' => $this->total(),
      'size' => $this->perPage(),
      'current_page' => $this->currentPage(),
      'total_pages' => $this->lastPage(),
    ];
  }
}
