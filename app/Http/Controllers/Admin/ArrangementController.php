<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arrangement;
use App\Models\Visualisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArrangementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $sort = $request->query('sort') === 'listens' ? 'listens' : 'created_at';

        $arrangements = Arrangement::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('code', $search)))
            ->orderByDesc($sort)
            ->paginate(15)
            ->withQueryString();

        return view('admin.arrangements.index', [
            'arrangements' => $arrangements,
            'visualisations' => Visualisation::ordered(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function destroy(Arrangement $arrangement): RedirectResponse
    {
        $arrangement->delete();

        return back()->with('status', "Arrangement {$arrangement->code} deleted.");
    }
}
