<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $query = Translation::with(['locale', 'tags'])
            ->when($request->key, fn($q, $key) => $q->where('key', 'like', "%$key%"))
            ->when($request->tag, fn($q, $tag) => $q->whereHas('tags', fn($q) => $q->where('name', $tag)))
            ->when($request->content, fn($q, $content) => $q->where('content', 'like', "%$content%"));

        return response()->json($query->paginate(20));
    }


    public function getAllTranslations(Request $request)
    {
        $query = Translation::with(['locale', 'tags']);

        if ($locale = $request->query('locale')) {
            $query->whereHas('locale', fn($q) => $q->where('code', $locale));
        }

        $translations = $query->get();
        return response()->json($translations);
    }


    public function export()
    {
        $translations = Translation::with(['locale', 'tags'])
            ->get(['key', 'locale_id', 'content'])
            ->map(function ($translation) {
                return [
                    'key' => $translation->key,
                    'locale' => $translation->locale->code,
                    'content' => $translation->content,
                    'tags' => $translation->tags->pluck('name'),
                ];
            });

        return response()->json($translations);
    }

    // Implement store(), update(), destroy() as needed...
}
