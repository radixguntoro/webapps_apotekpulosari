<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Model\ModelUsers;

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
    protected $redirectTo = '/';

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
        $input = $request->all();

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);
        $user = [];

        
        if (auth()->attempt(
                array(
                    'username' => $input['username'], 
                    'password' => $input['password'],
                    'status' => 1
                )
            )
        ) {
            $token = Auth::user()->createToken('ApotekPulosari')->accessToken;
            $user = array(
                "username" => auth()->user()->username,
                "permission" => auth()->user()->permission,
                "persons_id" => auth()->user()->persons_id,
                "token" => $token,
                "status" => 1
            );
            return response($user);
        } else {
            $user = array(
                "username" => null,
                "permission" => 0,
                "persons_id" => 0,
                "status" => 0
            );
            return response($user);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        if ($request->segment(1) == 'api') {
            return response()->json("loggedOut");
        } else {
            return redirect()->back();
        }
    }
}
