<?php

namespace App\Http\Controllers\AuthApi;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use JWTAuthException;
use App\User;
use App\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    /**
     * @SWG\Post(
     *   path="/api/login/",
     *   summary="login",
     *   operationId="register",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error"),
     * @SWG\Parameter(
     *          name="email",
     *          in="query",
     *           description="testing data",
     *          required=true,
     *          type="string",
     *     ),
     *@SWG\Parameter(
     *          name="password",
     *          in="query",
     *           description="testing data",
     *          required=true,
     *          type="string",
     *     ),
     *   )
     **/
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])/|required',
        ]);
        if ($validator->fails()) {
          return response()->json(['errors'=>$validator->errors()]);
        }
        if (! User::where('email',$request->email)->first())
        {
            return response()->json(['error'=> 'the Email is not found']);
        }

        // dd($request->all());
        $token = null;

        $expirationTime = env('JWT_TTL', 60 * 24 * 30);
        if ($request->remember_me == true) {
            $expirationTime = env('JWT_TTL', 60 * 24 * 30);
        }
        // else{
        //     $expirationTime = env('ttl',3);
        // }

        try {

            if (!User::where('email', $request['email'])->first()->confirmed) {
                return response()->json([
                    'response' => 'Error',
                    'message' => 'Sorry your account does not been activated yet',
                ]);
            }
            elseif (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'response' => 'Error',
                    'message' => 'Invalid Email or Password',
                ]);
            }
        }
        catch (JWTException $exception) {
            return response()->json([
                'response' => 'Error',
                'message' => 'Failed to create token',
            ]);
        }
        $tok = self::token($token);
        Auth::user()->remember_token = $tok;
        Auth::user()->update();
//
        return response()->json([
            'response' => 'Success',
            'message' => 'You logged in successfully',
            'token' => $tok,
            'expirationTime' => $expirationTime,
            'userId' => Auth::user()->id,
            'type' => Auth::user()->type
        ]);
    }
public  function token($token)
{
    $t =JWTAuth::setToken($token);
    $apy = JWTAuth::getPayload($t)->toArray();
    $apy['user_id'] = Auth::user()->id;
    $apy['type'] = Auth::user()->type;
    $factory = JWTFactory::customClaims($apy);
    $payload = $factory->make();
    $a = (string) JWTAuth::encode($payload);
    return $a;
}
    // Logout
     /**
     * @SWG\Post(
     *   path="/api/logout/",
     *   summary="User logout",
     *   operationId="register",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error"),
     * @SWG\Parameter(
     *          name="token",
     *          in="path",
     *           description="jwt token",
     *          required=true,
     *          type="string",
     *     ),
     *)
     **/

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }

        try {
            $t =JWTAuth::setToken($request->token);
            JWTAuth::invalidate($t);


            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
     // Check User Token
     /**
     * @SWG\Post(
     *   path="api/check-token/{user_id}/{token}",
     *   summary="Check User Token",
     *   operationId="token",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error"),
     * @SWG\Parameter(
     *          name="user Id",
     *          in="path",
     *           description="User Id",
     *          required=true,
     *          type="integer",
     *     ),
     *      * @SWG\Parameter(
     *          name="token",
     *          in="path",
     *           description="jwt token",
     *          required=true,
     *          type="string",
     *     ),
     *)
     **/
    public function checkToken($user_id, $token)
    {
        $user = User::query()->where('id', $user_id);
        if (count($user->get()) > 0) {
            $user = $user->first();
            if ($user->remember_token == $token) {
                $expirationTime = env('JWT_TTL', 60 * 24 * 30);
                return response()->json([
                    'response' => 'Success',
                    'message' => 'You logged in Successfully',
                    'token' => $token,
                    'expirationTime' => $expirationTime,

                ]);
            } else {
                return response()->json([
                    'response' => 'Error',
                    'message' => 'Token Expired',
                ]);
            }
        } else {
            return response()->json([
                'response' => 'Error',
                'message' => 'User Not Found',
            ]);
        }
    }
}
