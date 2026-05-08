<?php

namespace App\Http\Controllers;

use App\Services\GenreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class GenreController extends BaseController {
    public function __construct(private readonly GenreService $service) {
    }

    public function index(): JsonResponse {
        return Response::json($this->service->all());
    }
}
