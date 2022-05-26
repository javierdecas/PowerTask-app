<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;

class CheckApiToken
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
            if($request->header('api-token')){
                $token = $request->header('api-token');
                $student = Student::where('api_token', $token)->first();
                if($student){
                    $request->student = $student;
                    return $next($request);
                } else {
                    $response['response'] = "Api token not valid";
                    $http_status_code = 401;
                }
            } else {
                $response['response'] = "No api token";
                $http_status_code = 401;
            }
        } catch (\Throwable $th) {
            $response['response'] = $th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
