<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <title>Performance</title>
    <style>
        .table i.bi {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<div class="container">
    <table class="table">
        <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Info</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row">PHP Version</th>
            <td>{{ phpversion() }}</td>
        </tr>
        <tr>
            <th scope="row">IP Request</th>
            <td>{{ request()->getClientIp() }}</td>
        </tr>
        <tr>
            <th scope="row">Laravel Version</th>
            <td>{{ app()->version() }}</td>
        </tr>
        <tr>
            <th scope="row">Laravel Debug</th>
            <td>
                <i class="bi {{ config('app.debug') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2">Laravel Server Requirements</th>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Swoole Extension</td>
            <td>
                <i class="bi {{ extension_loaded('swoole') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">BCMath Extension</td>
            <td>
                <i class="bi {{ extension_loaded('bcmath') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Ctype Extension</td>
            <td>
                <i class="bi {{ extension_loaded('ctype') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">cURL Extension</td>
            <td>
                <i class="bi {{ extension_loaded('curl') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">DOM Extension</td>
            <td>
                <i class="bi {{ extension_loaded('dom') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Fileinfo Extension</td>
            <td>
                <i class="bi {{ extension_loaded('fileinfo') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">JSON Extension</td>
            <td>
                <i class="bi {{ extension_loaded('json') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Mbstring Extension</td>
            <td>
                <i class="bi {{ extension_loaded('mbstring') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">OpenSSL Extension</td>
            <td>
                <i class="bi {{ extension_loaded('openssl') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">PCRE Extension</td>
            <td>
                <i class="bi {{ extension_loaded('pcre') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">PDO Extension</td>
            <td>
                <i class="bi {{ extension_loaded('pdo') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Tokenizer Extension</td>
            <td>
                <i class="bi {{ extension_loaded('tokenizer') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">XML Extension</td>
            <td>
                <i class="bi {{ extension_loaded('xml') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2">gRPC</th>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Sockets Extension</td>
            <td>
                <i class="bi {{ extension_loaded('sockets') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">gRPC Extension</td>
            <td>
                <i class="bi {{ extension_loaded('grpc') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Protobuf Extension</td>
            <td>
                <i class="bi {{ extension_loaded('protobuf') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2">Extend</th>
        </tr>
        <tr>
            <td style="padding-left: 2rem">GMP Extension</td>
            <td>
                <i class="bi {{ extension_loaded('gmp') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">GD Extension</td>
            <td>
                <i class="bi {{ extension_loaded('gd') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Imagick Extension</td>
            <td>
                <i class="bi {{ extension_loaded('imagick') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Redis Extension</td>
            <td>
                <i class="bi {{ extension_loaded('redis') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">MongoDB Extension</td>
            <td>
                <i class="bi {{ extension_loaded('mongodb') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">ZIP Extension</td>
            <td>
                <i class="bi {{ extension_loaded('zip') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">Exif Extension</td>
            <td>
                <i class="bi {{ extension_loaded('exif') ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2">OPcache</th>
        </tr>
        <tr>
            <td style="padding-left: 2rem">opcache_enabled</td>
            <td>
                <i class="bi {{ $opcache['opcache_enabled'] ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2" style="padding-left: 2rem">memory_usage</th>
        </tr>
        @foreach($opcache['memory_usage'] as $key => $value)
            <tr>
                <td style="padding-left: 4rem">{{ $key }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
        <tr>
            <th scope="row" colspan="2" style="padding-left: 2rem">interned_strings_usage</th>
        </tr>
        @foreach($opcache['interned_strings_usage'] as $key => $value)
            <tr>
                <td style="padding-left: 4rem">{{ $key }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
        <tr>
            <th scope="row" colspan="2" style="padding-left: 2rem">opcache_statistics</th>
        </tr>
        @foreach($opcache['opcache_statistics'] as $key => $value)
            <tr>
                <td style="padding-left: 4rem">{{ $key }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
        <tr>
            <th scope="row" colspan="2" style="padding-left: 2rem">JIT</th>
        </tr>
        <tr>
            <td style="padding-left: 4rem">enabled</td>
            <td>
                <i class="bi {{ $opcache['jit']['enabled'] ? 'bi-check2-circle text-success' : 'bi-x-circle text-danger' }}"></i>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 4rem">buffer_size</td>
            <td>{{ round($opcache['jit']['buffer_size'] / 1024 / 1024, 1) }} MB</td>
        </tr>
        <tr>
            <td style="padding-left: 4rem">buffer_free</td>
            <td>{{ round($opcache['jit']['buffer_free'] / 1024 / 1024, 1) }} MB</td>
        </tr>
        <tr>
            <th scope="row" colspan="2">Info</th>
        </tr>
        <tr>
            <td style="padding-left: 2rem">upload_max_filesize</td>
            <td>{{ ini_get('upload_max_filesize') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">max_file_uploads</td>
            <td>{{ ini_get('max_file_uploads') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">post_max_size</td>
            <td>{{ ini_get('post_max_size') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">max_input_vars</td>
            <td>{{ ini_get('max_input_vars') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">max_execution_time</td>
            <td>{{ ini_get('max_execution_time') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">disable_functions</td>
            <td>{{ ini_get('disable_functions') }}</td>
        </tr>
        <tr>
            <td style="padding-left: 2rem">short_open_tag</td>
            <td>{{ ini_get('short_open_tag') }}</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>