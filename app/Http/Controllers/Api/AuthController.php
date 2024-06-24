<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post (
     ** path="/api/register",
     *    tags={"Регистрация"},
     *    summary="Регистрация",
     * @OA\Parameter(
     *        name="name",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *             type="string"
     *        )
     *   ),
     * @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *            type="string"
     *       )
     *  ),
     *      @OA\Parameter(
     *        name="password",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *             type="string"
     *        )
     *   ),
     *   @OA\Parameter(
     *         name="c_password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *    ),
     *     @OA\Response(
     *       response=200,
     *       description="Успешно",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="success", type="boolean", example="true"),
     *           @OA\Property(property="message", type="string", example="Пользователь успешно зарегистрирован"),
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
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Ошибка валидации.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApi')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'Пользователь успешно зарегистрирован.');
    }

    /**
     * @OA\Post (
     ** path="/api/login",
     *    tags={"Логин"},
     *    summary="Логин",
     * @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *            type="string"
     *       )
     *  ),
     *      @OA\Parameter(
     *        name="password",
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
     *           @OA\Property(property="message", type="string", example="Пользователь успешно авторизован"),
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

    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['name'] =  $user->name;

            return $this->sendResponse($success, 'Пользователь успешно авторизован.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }
}
