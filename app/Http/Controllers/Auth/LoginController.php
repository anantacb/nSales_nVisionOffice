<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Office\User;
use App\Providers\RouteServiceProvider;
use App\Transformer\ApiResponseTransformer;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $loginResponse = $this->attemptLogin($request);
        if ($loginResponse['status'] && $loginResponse['token']) {
            return $this->respondWithToken($loginResponse['token']);
        }

        return $this->sendFailedLoginResponse($request, $loginResponse['message']);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        $user = User::with('companyUsers.roles')->where('Email', $request->get('email'))->first();
        // check user exist
        if (!$user) {
            return [
                'message' => 'These credentials do not match our records.',
                'status' => false,
            ];
        }

        // check is user is active
        if ($user->Disabled) {
            return [
                'message' => 'User is not active.',
                'status' => false,
            ];
        }

        // check is user is locked
        if ($user->IsLocked) {
            return [
                'message' => 'Your account is locked.',
                'status' => false,
            ];
        }

        // check user have associate companies
        if ($user->companyUsers->count() <= 0) {
            return [
                'message' => 'You are not associate with any company.',
                'status' => false,
            ];
        }

        $roles = [];
        $user->companyUsers->pluck('roles')->each(function ($roleArr) use (&$roles) {
            $roleArr->pluck('Type')->each(function ($role) use (&$roles) {
                $roles[$role] = $role;
            });
        });
        if (!$roles["Developer"]) {
            return [
                'message' => 'You are not developer in any company.',
                'status' => false,
            ];
        }

        $psw = sha1($user->Salt . $request->get('password'));
        $psw = strtoupper($psw);

        // auth user
        if ($user->Hash == $psw) {
            $token = Auth::login($user);
            return [
                'message' => 'Successfully login.',
                'status' => true,
                'token' => $token
            ];
        }

        return [
            'message' => 'These credentials do not match our records.',
            'status' => false,
        ];
    }

    protected function respondWithToken($token)
    {
        return ApiResponseTransformer::success(
            [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expire_at' => strtotime(Carbon::now()->toDateTimeString()),
            ],
            "Authentication successful!"
        );
    }

    protected function sendFailedLoginResponse(Request $request, $message)
    {
        return ApiResponseTransformer::error([], $message, 422);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return ApiResponseTransformer::success(
            [],
            'Successfully logged out'
        );
    }
}
