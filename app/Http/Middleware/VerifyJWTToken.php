<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JwtException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class VerifyJWTToken
{
	public $response;
	public $user;
	public $token;
	public $refreshedToken;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		//var_dump('JWTAuth::getToken()');die;
		try
		{
			$this->token = $request->header('Authorization');
			$this->user = JWTAuth::toUser($this->token);
		}
		catch (JWTException $e) 
		{
			if($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) 
			{
				return response()->json(['token_invalid'], $e->getStatusCode());
			}
			else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) 
			{
				//$token = JWTAuth::getToken();
				//var_dump($this->token);die;
				if(!$this->user)
				{
					$this->response = [
						'status' => 'Token Expired',
						'newToken' => JWTAuth::refresh($this->token)
					];
					return response()->json($this->response, 401);
				}
				//var_dump(JWTAuth::refresh($this->token));die;
				try
				{
					//$refreshedToken = JWTAuth::refresh($token);
					
				}
				catch (JWTException $e)
				{
					//var_dump($this->response);die;
					//return $this->response->errorInternal('Not able to refresh Token');
					return response()->json(['Not able to refresh Token'], $e->getStatusCode());
					
				}
				return response()->json(['error'=>'token_expired','token'=>$refreshedToken]);
			}
			else
			{
				return response()->json(['error'=>'Token is Required']);
			}
		}
        return $next($request);
    }
}

