<?php

namespace App\Http\Controllers;

use App\Models\Arrangement;
use App\Models\Page;
use App\Models\Visualisation;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return $this->render();
    }

    public function arrangement(Arrangement $arrangement): View
    {
        $arrangement->recordListen();

        return $this->render($arrangement);
    }

    private function render(?Arrangement $arrangement = null): View
    {
        $visualisations = Visualisation::ordered();

        return view('home', [
            'page' => Page::where('slug', 'home')->firstOrFail(),
            'visualisations' => $visualisations,
            'arrangement' => $arrangement,
            'playerConfig' => $visualisations->map->toPlayerConfig()->values(),
        ]);
    }
}
