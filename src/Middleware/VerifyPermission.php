<?php

namespace WalkerChiu\Role\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class VerifyPermission
{
    const DELIMITER = '|';

    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard  $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param Closure                   $next
     * @param $permissions
     * @return Mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if (
            $this->auth->guest()
            || !$request->user()->canDo($permissions)
        ) {
            if ($request->expectsJson()) {
                if (config('wk-core.formRequest.returnType') == 1) {
                    abort(403);
                } elseif (config('wk-core.formRequest.returnType') == 2) {
                    return new JsonResponse([
                            'action' => trans('php-role::permission.unauthenticated')
                        ], 403);
                } elseif (config('wk-core.formRequest.returnType') == 3) {
                    return response()->json([
                            'success' => false,
                            'data'    => null,
                            'error'   => [
                                'action' => trans('php-role::permission.unauthenticated')
                            ]
                        ], 403, [
                            'Content-Type' => 'application/json',
                            'Charset'      => 'utf-8'
                        ], JSON_UNESCAPED_UNICODE);
                }
            }

            return redirect()
                        ->route(config('wk-role.redirect.permission'))
                        ->with([
                            'success' => false,
                            'message' => trans('php-role::permission.unauthenticated')
                        ]);
        }

        return $next($request);
    }
}
