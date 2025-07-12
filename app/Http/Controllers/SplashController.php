<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SplashController extends Controller
{
    /**
     * Show the splash screen.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('splash');
    }
}