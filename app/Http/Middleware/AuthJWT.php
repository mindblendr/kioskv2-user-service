<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Redis;

class AuthJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        try {
            if ($request->header('Authorization')) {
                $token = explode(' ', $request->header('Authorization'))[1];
                $decoded_token = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));

                $redis_token = Redis::get($decoded_token->sub);
                $redis_decoded_token = JWT::decode($redis_token, new Key(config('jwt.secret'), config('jwt.algo')));

                if ($token === $redis_token) {
                    
                    if (is_null($guard) || count(array_unique([$guard, $redis_decoded_token->type, $decoded_token->type])) === 1) {
                        $request->attributes->add(['user_data' => User::where(['id' => $redis_decoded_token->sub])->first()]);
                        return $next($request);
                    }
                }
            }
        } catch (\Throwable $th) {
            // return response(['message' => $th->getMessage()], 401);
        }
        return response(['message' => 'Unauthorized!'], 401);
    }
}
