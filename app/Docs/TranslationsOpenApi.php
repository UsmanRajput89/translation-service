<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Translations",
 *     description="API Endpoints for managing translations"
 * )
 */
class TranslationsOpenApi
{
    /**
     * @OA\Post(
     *     path="/api/translations/create",
     *     summary="Create a new translation",
     *     description="Create a new translation with all it's translations",
     *     tags={"Translations"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"key", "translations"},
     *                 @OA\Property(property="key", type="string"),
     *                 @OA\Property(
     *                     property="translations",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="locale", type="string", description="The locale of the translation"),
     *                         @OA\Property(property="content", type="string", description="The content of the translation"),
     *                         @OA\Property(
     *                             property="tags",
     *                             type="array",
     *                             description="The tags of the translation",
     *                             @OA\Items(type="integer", description="The id of the tag")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created"
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     summary="Update a translation",
     *     description="Update a translation",
     *     tags={"Translations"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         description="ID of translation to return",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="content",
     *                     type="string",
     *                     description="The content of the translation"
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     description="The tags of the translation",
     *                     @OA\Items(type="integer", description="The id of the tag")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(response=400, description="Invalid ID supplied"),
     *     @OA\Response(response=404, description="Translation not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function update() {}

    /**
     * @OA\Get(
     *     path="/api/translations/{id}",
     *     summary="Get a single translation",
     *     tags={"Translations"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/api/translations/search",
     *     summary="Search translations",
     *     description="Search translations by key, content or tags",
     *     tags={"Translations"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         description="Search query",
     *         in="query",
     *         name="query",
     *         required=false,
     *         example="Hello World"
     *     ),
     *     @OA\Parameter(
     *         description="Array of tag IDs",
     *         in="query",
     *         name="tags",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="integer", example=1)
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Locale ID",
     *         in="query",
     *         name="locale",
     *         required=true,
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(response=400, description="Invalid ID supplied"),
     *     @OA\Response(response=404, description="Translation not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function search() {}

    /**
     * @OA\Get(
     *     path="/api/translations/export",
     *     summary="Export translations",
     *     description="Export translations for a given locale",
     *     tags={"Translations"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         description="Locale code",
     *         in="query",
     *         name="locale",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(response=422, description="Locale parameter is required"),
     *     @OA\Response(response=404, description="Invalid locale code"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function export() {}
}