<?php

namespace App\Http\Middleware;

use App\Models\UserLanguage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if ($request->headers->has('Accept-language')) {
            $lang = $request->header('Accept-language', "en");
            if (in_array($lang, ["en", "ar"])) {
                App::setLocale($lang);
            }
        }

        return $next($request);
    }
}
