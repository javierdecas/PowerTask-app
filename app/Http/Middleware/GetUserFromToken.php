<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GetUserFromToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->header('token');
            if($token) {
                $user = Socialite::driver('google')->userFromToken($request->header('token'));
                $request->user = $user;
                return $next($request);
            } else {
                Log::channel('errors')->info('[app/Http/Middleware/GetUserFromToken.php] No token');
                $response['response'] = "No token";
                $http_status_code = 412;
            }
        } catch (\Throwable $th) {
            if(Str::contains($th, '401 Unauthorized') && Str::contains($th, 'Invalid Credentials')) {
                $response['response'] = 'Google token not valid';
                $http_status_code = 401;
            } else {
                $response['response'] = $th->getMessage();
                $http_status_code = 500;
            }
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
