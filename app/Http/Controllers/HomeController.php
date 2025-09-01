<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IksReportService;

class HomeController
{
    public function index()
    {
        $iksService = app(IksReportService::class);

        return view('home', [
            // Data lain yang mungkin Anda perlukan
        ]);
    }
}
