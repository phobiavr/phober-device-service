<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthServer {
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return Response|RedirectResponse|JsonResponse
   */
  public function handle(Request $request, Closure $next) {
    $response = Http::accept('application/json')->withHeaders([
      'Authorization' => 'Bearer ' . request()->bearerToken()
    ])->get(config('app.auth_server') . '/valid');

    if ($response->status() === Response::HTTP_OK) {
      Auth::guard('server')->setUser($response['user']);

      return $next($request);
    }

    return response()->json(['message' => 'Credentials error'], 401);
  }
}
