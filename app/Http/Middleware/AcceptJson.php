<?php

namespace App\Http\Middleware;

use App\Libs\Encrypt\EDData;
use App\Libs\Encrypt\EDFile;
use App\Libs\Setting\DataClient;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AcceptJson
{
    /**
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('options')) {
            return response('OK', SymfonyResponse::HTTP_NO_CONTENT);
        }
        if ($request->hasHeader('os-data')) {
            if ($os_data = $request->header('os-data')) {
                if ($decode_osa = EDData::getData($os_data)) {
                    if (isset($decode_osa['e']) && $decode_osa['e']) {
                        if ($decode_osa['e'] < strtotime('now')) {
                            return response('ExpiredOverdue', SymfonyResponse::HTTP_BAD_REQUEST);
                        }
                    }

                    $data_client = new DataClient($decode_osa);
                    $data_client->initAgent($request);

                    setting()->dataClient = $data_client;
                }
            }
        } else {
            $os_data = [];

            if ($request->hasHeader('platform')) {
                $os_data['platform'] = $request->header('platform');
            }

            if ($request->hasHeader('langCode')) {
                $os_data['langCode'] = $request->header('langCode');
            }
            setting()->dataClient = new DataClient($os_data);
        }
        if (setting()->dataClient?->isEncrypt()) {
            if ($bearerToken = $request->bearerToken()) {
                if ($det = EDData::getData($bearerToken)) {
                    if (empty($det['t'])) {
                        return response('Unauthorized.LostT', SymfonyResponse::HTTP_UNAUTHORIZED);
                    } elseif (empty($det['e'])) {
                        return response('Unauthorized.LostE', SymfonyResponse::HTTP_UNAUTHORIZED);
                    } elseif ($det['e'] < strtotime('now')) {
                        return response('Unauthorized.Overdue', SymfonyResponse::HTTP_UNAUTHORIZED);
                    }

                    $reToken = $det['t'];

                    $request->headers->set('Authorization', "Bearer $reToken");
                }
            }
            if ($segment = $request->segment(2)) {
                if ($decode = EDFile::getLinkData($segment)) {
                    if (empty($decode['e'])) {
                        return response('ExpiredLost', SymfonyResponse::HTTP_BAD_REQUEST);
                    } elseif ($decode['e'] < strtotime('now')) {
                        return response('ExpiredOverdue', SymfonyResponse::HTTP_BAD_REQUEST);
                    }

                    if (!empty($decode['q'])) {
                        $request->query->replace($decode['q']);
                    }

                    if (!$request->isMethod('GET')) {
                        if ($content = $request->getContent()) {
                            if ($dec = EDData::getData($content)) {
                                $content = $dec;
                            }
                        }
                    }

                    if (!empty($decode['p'])) {
                        $uri = str_replace($segment, $decode['p'], $request->server->get('REQUEST_URI'));

                        $proxy = SymfonyRequest::create(
                            $uri,
                            $request->method(),
                            $request->all(),
                            $request->cookies->all(),
                            $request->files->all(),
                            $request->server->all(),
                            !empty($content) ? json_encode($content) : $request->getContent()
                        );

                        if (runningInOctane()) {
                            if (!empty($content) && is_array($content)) {
                                $proxy->request->add($content);
                            }
                        }

                        return app()->handle($proxy);
                    }
                }
            }
        }

        return $next($request);
    }
}
