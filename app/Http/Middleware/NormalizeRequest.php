<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class NormalizeRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        foreach ($request->post() as $key => $value) {
            if (is_string($value)) {
                if (Str::lower($value) === "true") {
                    $request->request->set($key, true);
                } elseif (Str::lower($value) === "false") {
                    $request->request->set($key, false);
                } elseif (Str::lower($value) === 'undefined') {
                    $request->request->remove($key);
                } else {
                    $request->request->set($key, trim($value));
                }
            } elseif (is_array($value)) {
                $request->request->set($key, $this->arrayTrimString($value));
            }
            switch ($key) {
                case 'limit':
                case 'page':
                    if (is_string($value)) {
                        if(!intval(trim($value))) {
                            $request->query->remove($key);
                        }
                    }
                    break;
            }
        }
        foreach ($request->query() as $key => $value) {
            $key = Str::lower($key);

            if ($key == 'order') {
                if ($value == 'descend') {
                    $request->query->set($key, 'desc');
                } elseif ($value == 'ascend') {
                    $request->query->set($key, 'asc');
                }
            } elseif ($key == 'sort') {
                $request->query->set($key, Str::snake($value));
            }
            switch ($key) {
                case 'limit':
                case 'page':
                    if (is_string($value)) {
                        if(!intval(trim($value))) {
                            $request->query->remove($key);
                        }
                    }
                    break;
            }
            if (is_string($value)) {
                switch (Str::lower($value)) {
                    case 'true':
                        $request->query->set($key, true);

                        break;
                    case 'false':
                        $request->query->set($key, false);

                        break;
                }
            }
        }

        return $next($request);
    }

    protected function arrayTrimString($arr): array
    {
        $data = [];

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->arrayTrimString($value);
            } elseif (is_string($value)) {
                if (Str::lower($value) === 'undefined') {
                    continue;
                } elseif (Str::lower($value) === "true") {
                    $data[$key] = true;
                } elseif (Str::lower($value) === "false") {
                    $data[$key] = false;
                } else {
                    $data[$key] = trim($value);
                }
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
