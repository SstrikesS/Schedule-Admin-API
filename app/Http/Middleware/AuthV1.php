<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthV1 extends Middleware
{
    /**
     * @return Response|RedirectResponse
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
////        try {
////            $this->authenticate($request, $guards);
////        } catch (AuthenticationException) {
////            return $this->_response();
////        }
//
        $user = $request->user();
        if ($user instanceof User) {
            if (!$user->getAttribute('status')) {
                return $this->_response();
            }
        } else {
            return $this->_response();
        }

        return $next($request);
    }

    protected function redirectTo($request): ?string
    {
        throw new HttpResponseException($this->_response());
    }

    protected function _response(): Response|Application|ResponseFactory
    {
        return response('Authorization', SymfonyResponse::HTTP_UNAUTHORIZED);
    }
}
