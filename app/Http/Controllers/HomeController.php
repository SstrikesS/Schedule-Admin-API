<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;


class HomeController extends Controller
{
    public function index(): string
    {
        return "API-AMC SLF";
    }

    public function performance(): View\Factory|View\View
    {
        $data = [];

        $data['opcache'] = opcache_get_status();

        return view('performance', $data);
    }
    public function redImage(Request $request)
    {
        if($email_id = $request->get('email')){
            DB::connection('pgsql_main')
                ->table('queue.email_status')
                ->where('id', $email_id)
                ->update(
                    [
                        'seen_at' => now(),
                    ]
                );
        }

        return  Image::make(storage_path('app/public/images/Red_Color.jpg'))->resize(1,1)->response('jpg');
    }
}
