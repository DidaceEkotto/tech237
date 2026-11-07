<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class StaticPageController extends Controller
{
    public function support()
    {
        return Inertia::render('Support');
    }

    public function terms()
    {
        return Inertia::render('Legal/Terms');
    }

    public function privacy()
    {
        return Inertia::render('Legal/Privacy');
    }
}
