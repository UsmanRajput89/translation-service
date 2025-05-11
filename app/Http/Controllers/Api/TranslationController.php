<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use App\Models\Translation;
use DB;
use Illuminate\Http\Request;
class TranslationController extends Controller
{
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

    public function update(Request $request, $id)
    {
        $translation = Translation::findOrFail($id);

        $validated = $request->validate([
            'content' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Don't allow 'key' to be updated
        if ($request->has('key')) {
            return response()->json(['error' => 'You cannot update the key of a translation.'], 403);
        }

        // Update content if provided
        if (isset($validated['content'])) {
            $translation->content = $validated['content'];
            $translation->save();
        }

        // Sync tags if provided
        if (isset($validated['tags'])) {
            $translation->tags()->sync($validated['tags']);
        }

        return response()->json([
            'message' => 'Translation updated successfully.',
            'data' => $translation->load('tags', 'locale')
        ]);
    }

    public function show($id)
    {
        $translation = Translation::with(['tags:id,name', 'locale:id,code,name'])->findOrFail($id);

        return response()->json([
            'id' => $translation->id,
            'key' => $translation->key,
            'content' => $translation->content,
            'locale' => $translation->locale,
            'tags' => $translation->tags
        ]);
    }

    public function search(Request $request)
    {
        $searchQuery = $request->query('query');
        $tags = $request->query('tags');
        $localeId = $request->query('locale');

        if (!$localeId) {
            return response()->json([
                'message' => 'locale parameter is required.'
            ], 422);
        }
        if (!$searchQuery && !$tags) {
            return response()->json([
                'message' => 'Please provide Search query or tags.'
            ], 422);
        }

        $bindings = [];
        $where = '';
        $tagCount = is_array($tags) ? count($tags) : 0;

        if ($localeId) {
            $where .= " AND t.locale_id = ?";
            $bindings[] = $localeId;
        }

        if ($searchQuery) {
            $where .= " AND (t.key LIKE ? OR t.content LIKE ?)";
            $bindings[] = "%$searchQuery%";
            $bindings[] = "%$searchQuery%";
        }

        if ($tagCount > 0) {
            $placeholders = implode(',', array_fill(0, $tagCount, '?'));
            $where .= " AND tg.id IN ($placeholders)";
            $bindings = array_merge($bindings, $tags);
        }

        $sql = "
            SELECT 
                t.id,
                t.key,
                t.content,
                t.locale_id,
                l.code as locale_code,
                tg.id as tag_id,
                tg.name as tag_name
            FROM translations t
            JOIN locales l ON l.id = t.locale_id
            JOIN tag_translation tt ON tt.translation_id = t.id
            JOIN tags tg ON tg.id = tt.tag_id
            WHERE 1=1 $where
        ";

        // Add HAVING only if tag filter applied
        if ($tagCount > 0) {
            $sql .= "
            GROUP BY t.id, tg.id, t.key, t.content, t.locale_id, l.code
            HAVING (
                SELECT COUNT(DISTINCT tt2.tag_id)
                FROM tag_translation tt2
                WHERE tt2.translation_id = t.id
                AND tt2.tag_id IN (" . implode(',', $tags) . ")
            ) = $tagCount
        ";
        } else {
            $sql .= " GROUP BY t.id, tg.id, t.key, t.content, t.locale_id, l.code";
        }

        $raw = DB::select($sql, $bindings);

        if (empty($raw)) {
            return response()->json([
                'message' => 'No translations found matching the given criteria.'
            ], 404);
        }

        $translations = collect($raw)->groupBy('id')->map(function ($items) {
            $first = $items->first();
            return [
                'id' => $first->id,
                'key' => $first->key,
                'content' => $first->content,
                'locale_id' => $first->locale_id,
                'locale_code' => $first->locale_code,
                'tags' => $items->map(fn($item) => [
                    'id' => $item->tag_id,
                    'name' => $item->tag_name,
                ])->unique('id')->values(),
            ];
        })->values();

        return response()->json($translations);
    }

    public function export(Request $request)
    {
        $localeCode = $request->query('locale');

        if ($localeCode) {
            $localeCode = filter_var($localeCode, FILTER_SANITIZE_STRING);
        }

        if (!$localeCode) {
            return response()->json(['error' => 'locale parameter is required.'], 422);
        }

        $locale = Locale::where('code', $localeCode)->first();

        if (!$locale) {
            return response()->json(['error' => 'Invalid locale code.'], 404);
        }

        // Fetch translations as an array
        $translations = Translation::where('locale_id', $locale->id)
            ->pluck('content', 'key')
            ->toArray(); // Ensure it's an array for the JSON response

        return response()->json($translations);
    }



}
