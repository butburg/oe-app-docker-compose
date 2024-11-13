<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user() && auth()->user()->usertype == 'admin') {
            return $next($request);
        }
        return redirect('/');
    }
}
