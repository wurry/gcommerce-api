<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class RefreshToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		$response = $next($request);
		
		try
		{
			if(!$user = JWTAuth::parseToken()->authenticate()
			{
				return ApiHelpers::ApiResponse(101, null);
			}
		}
		catch (TokenExpiredException $e)
		{
			try
			{
				$refreshed = JWTAuth::refresh(JWTAuth::getToken());
				$response->header('Authorization', 'Bearer ' . $refreshed);
			}
			catch (JWTException $e)
			{
				return ApiHelpers::ApiResponse(103, null);
			}
			$user = JWTAuth::setToken($refreshed)->toUser();
		}
		catch (JWTException $e)
		{
			return ApiHelpers::ApiResponse(101, null);
		}
		
		Auth::login($user, false);
		
		
        return $response;
    }
}
