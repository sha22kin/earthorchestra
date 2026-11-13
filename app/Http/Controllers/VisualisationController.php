<?php

namespace App\Http\Controllers;

use App\Models\Visualisation;
use Illuminate\View\View;

class VisualisationController extends Controller
{
    public function show(string $slug): View
    {
        $all = Visualisation::ordered();
        $index = $all->search(fn (Visualisation $v) => $v->slug === $slug);

        abort_if($index === false, 404);

        return view('visualisations.show', [
            'visualisation' => $all[$index],
            'previous' => $all->get($index - 1),
            'next' => $all->get($index + 1),
        ]);
    }
}
