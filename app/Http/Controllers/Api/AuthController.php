<?php

namespace App\Http\Controllers\Api;

use App\Services\PassportService;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login (Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = PassportService::token($user);
                $response = [ 'token' => $token, 'user' => $user ];
                return response($response, 200);
            } else {
                $response = [
                    'message' => 'The given data was invalid.',
                    'errors' => [ 'password' => [ 'Password missmatch' ] ]
                ];
                return response($response, 422);
            }
        } else {
            $response = [
                'message' => 'The given data was invalid.',
                'errors' => [ 'email' => [ 'User does not exist' ] ]
            ];
            return response($response, 422);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout (Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = [ 'message' => 'You have been succesfully logged out!' ];
        return response($response, 200);
    }


}
