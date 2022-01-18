<?php

namespace WalkerChiu\Role\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class VerifyRole
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
     * @param $roles
     * @return Mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        if (!is_array($roles)) {
            $roles = explode(self::DELIMITER, $roles);
        }

        if (
            $this->auth->guest()
            || !$request->user()->hasRole($roles)
        ) {
            if ($request->expectsJson()) {
                if (config('wk-core.formRequest.returnType') == 1) {
                    abort(403);
                } elseif (config('wk-core.formRequest.returnType') == 2) {
                    return new JsonResponse([
                            'action' => trans('php-role::role.unauthenticated')
                        ], 403);
                } elseif (config('wk-core.formRequest.returnType') == 3) {
                    return response()->json([
                            'success' => false,
                            'data'    => null,
                            'error'   => [
                                'action' => trans('php-role::role.unauthenticated')
                            ]
                        ], 403, [
                            'Content-Type' => 'application/json',
                            'Charset'      => 'utf-8'
                        ], JSON_UNESCAPED_UNICODE);
                }
            }

            return redirect()
                        ->route(config('wk-role.redirect.role'))
                        ->with([
                            'success' => false,
                            'message' => trans('php-role::role.unauthenticated')
                        ]);
        }

        return $next($request);
    }
}
