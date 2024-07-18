<?php

namespace App\Http\Middleware;

use App\Http\Resources\BaseResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();
        if ($user->role != 'user') {
            return $next($request);
        }
        return BaseResource::returns('Unauthorized!', 401);
    }
}
