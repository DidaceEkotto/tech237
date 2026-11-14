<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PortfolioTemplate;
use App\Services\Portfolio\PageTreeValidator;
use Illuminate\Http\Request;

class PortfolioTemplateController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $templates = PortfolioTemplate::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('is_public', true);
        })->latest()->get();

        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request, PageTreeValidator $validator)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'scope' => ['required', 'in:section,row,module,page'],
            'category' => ['nullable', 'string', 'max:60'],
            'tree_payload' => ['required', 'array'],
        ]);

        $payload = $validated['scope'] === 'page'
            ? $validator->sanitizeTree($validated['tree_payload'])
            : $validator->sanitizeTree(['sections' => [$validated['tree_payload']]]);

        $finalPayload = $validated['scope'] === 'page'
            ? $payload
            : ($payload['sections'][0] ?? $validated['tree_payload']);

        $template = PortfolioTemplate::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'scope' => $validated['scope'],
            'category' => $validated['category'] ?? 'Personnel',
            'tree' => $finalPayload,
            'is_public' => false,
        ]);

        return response()->json([
            'template' => $template,
            'message' => 'Modèle enregistré dans votre bibliothèque.',
        ], 201);
    }

    public function destroy(PortfolioTemplate $template, Request $request)
    {
        if ($template->user_id !== $request->user()->id || $template->is_public) {
            abort(403);
        }

        $template->delete();

        return response()->json(['message' => 'Modèle supprimé.']);
    }
}
