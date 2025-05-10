<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locale;
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
            ->groupBy('t.id', 'tg.id')
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


    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'translations' => 'required|array|min:1',
            'translations.*.locale' => 'required|exists:locales,code', 
            'translations.*.content' => 'nullable|string',
            'translations.*.tags' => 'array',
            'translations.*.tags.*' => 'exists:tags,id',
        ]);

        foreach ($request->translations as $t) {
            $locale = Locale::where('code', $t['locale'])->first();

            $translation = Translation::create([
                'key' => $request->key,
                'locale_id' => $locale->id,
                'content' => $t['content'] ?? '',
            ]);

            if (!empty($t['tags'])) {
                $translation->tags()->sync($t['tags']);
            }
        }

        return response()->json(['message' => 'Translations created successfully.'], 201);
    }



}
