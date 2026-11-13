<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Visualisation;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'page' => Page::where('slug', 'about')->firstOrFail(),
        ]);
    }

    public function credits(): View
    {
        return view('pages.credits', [
            'page' => Page::where('slug', 'credits')->firstOrFail(),
            'visualisations' => Visualisation::ordered(),
        ]);
    }
}
