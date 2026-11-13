<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArrangementRequest;
use App\Models\Arrangement;
use Illuminate\Http\JsonResponse;

class ArrangementController extends Controller
{
    public function store(StoreArrangementRequest $request): JsonResponse
    {
        $arrangement = Arrangement::create([
            'code' => Arrangement::freshCode(),
            'title' => $request->cleanTitle(),
            'levels' => $request->levels(),
            'creator_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key')),
        ]);

        return response()->json([
            'code' => $arrangement->code,
            'title' => $arrangement->displayTitle(),
            'url' => $arrangement->shareUrl(),
        ], 201);
    }

    public function show(Arrangement $arrangement): JsonResponse
    {
        return response()->json([
            'code' => $arrangement->code,
            'title' => $arrangement->displayTitle(),
            'levels' => $arrangement->levels,
            'listens' => $arrangement->listens,
            'created_at' => $arrangement->created_at->toIso8601String(),
        ]);
    }
}
