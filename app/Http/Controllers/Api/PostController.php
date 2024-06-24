<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * @OA\Get (
     ** path="/api/posts",
     *   tags={"Посты"},
     *   summary="Посты",
     *   security={ {"bearer": {} }},
     *   @OA\Response(
     *      response=200,
     *       description="Успешно"
     *   )
     *)
     *
     * @return JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $posts = Post::paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $response = Http::get("https://dummyjson.com/posts/{$post->dummy_post_id}");
            $dummyPost = $response->json();
            return [
                'id' => $post->id,
                'title' => $dummyPost['title'] ?? '',
                'author_name' => User::where('id', $post->user_id)->pluck('name')->first(),
                'description' => substr($dummyPost['body'] ?? '', 0, 128),
            ];
        });

        return $this->sendResponse($posts, 'Записи успешно загружены.');
    }

    /**
     * @OA\Post (
     ** path="/api/posts",
     *    tags={"Посты"},
     *    summary="Добавление",
     *    security={ {"bearer": {} }},
     * @OA\Parameter(
     *       name="title",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *            type="string"
     *       )
     *  ),
     *      @OA\Parameter(
     *        name="body",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *             type="string"
     *        )
     *   ),
     *     @OA\Response(
     *       response=200,
     *       description="Успешно",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="success", type="boolean", example="true"),
     *           @OA\Property(property="message", type="string", example="Запись успешно добавлена"),
     *       )
     *    ),
     *    @OA\Response(
     *       response=422,
     *       description="Ошибка проверки"
     *    )
     * )
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $dummyPost = Http::post('https://dummyjson.com/posts/add', [
            'title' => $request->title,
            'body' => $request->body,
            'userId' => Auth::id(),
        ])->json();

        $post = Post::create([
            'user_id' => Auth::id(),
            'dummy_post_id' => $dummyPost['id'],
        ]);

        return $this->sendResponse($dummyPost, 'Запись успешно добавлена.');
    }


    /**
     * @OA\Put (
     ** path="/api/posts/{post}",
     *    tags={"Посты"},
     *    summary="Редактирование",
     *    security={ {"bearer": {} }},
     * @OA\Parameter(
     *        name="post",
     *        in="path",
     *        required=true,
     *        @OA\Schema(
     *             type="integer"
     *        )
     *   ),
     * @OA\Parameter(
     *       name="title",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *            type="string"
     *       )
     *  ),
     *      @OA\Parameter(
     *        name="body",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *             type="string"
     *        )
     *   ),
     *     @OA\Response(
     *       response=200,
     *       description="Успешно",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="success", type="boolean", example="true"),
     *           @OA\Property(property="message", type="string", example="Запись успешно добавлена"),
     *       )
     *    ),
     *    @OA\Response(
     *       response=422,
     *       description="Ошибка проверки"
     *    )
     * )
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function update(Request $request, Post $post)
    {
        if ($post->user_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Http::put("https://dummyjson.com/posts/{$post->dummy_post_id}", [
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return $this->sendResponse([], 'Запись успешно обновлена.');
    }

    public function destroy(Post $post)
    {
        if ($post->user_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Http::delete("https://dummyjson.com/posts/{$post->dummy_post_id}");
        $post->delete();

        return $this->sendResponse([], 'Запись успешно удалена.');
    }
}

