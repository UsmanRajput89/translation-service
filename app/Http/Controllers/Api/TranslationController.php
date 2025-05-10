<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use DB;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function getAllTranslations(Request $request)
    {
        $localeId = $request->query('locale');
        $tags = $request->query('tags');

        if (!$localeId || !$tags || !is_array($tags)) {
            return response()->json(['error' => 'Missing locale or tags parameter.'], 422);
        }

        $raw = DB::table('translations as t')
            ->select(
                't.id',
                't.key',
                't.content',
                't.locale_id',
                'l.code as locale_code',
                'tg.id as tag_id',
                'tg.name as tag_name'
            )
            ->join('locales as l', 'l.id', '=', 't.locale_id')
            ->join('tag_translation as tt', 'tt.translation_id', '=', 't.id')
            ->join('tags as tg', 'tg.id', '=', 'tt.tag_id')
            ->where('t.locale_id', $localeId)
            ->whereIn('tg.id', $tags)
            ->get();

        $translations = collect($raw)->groupBy('id')->map(function ($items) {
            $first = $items->first();
            return [
                'id' => $first->id,
                'key' => $first->key,
                'content' => $first->content,
                'locale_id' => $first->locale_id,
                'locale_code' => $first->locale_code,
                'tags' => $items->map(function ($item) {
                    return [
                        'id' => $item->tag_id,
                        'name' => $item->tag_name,
                    ];
                })->unique('id')->values(),
            ];
        })->values();


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
