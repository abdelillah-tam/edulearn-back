<?php

namespace App\Http\Controllers;

use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Log;

class UserController extends Controller
{
    //

    public function signup(SignupRequest $request)
    {

        $validated = $request->validated();

        $password = Hash::make($validated["password"]);

        $query = DB::table('users')->insert([
            'fullname' => $validated['user']['fullname'],
            'email' => $validated['user']['email'],
            'password' => $password,
            'type' => $validated['user']['type']
        ]);

        return response()->json($query);
    }

    public function signin(SigninRequest $request)
    {

        Auth::check();

        if (Auth::attempt($request->validated())) {
            $request->session()->regenerate();

            $request->session()->save();

            return response()->json(true);
        }

        return response()->json(false);
    }


    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json('done');

    }

      public function test(Request $request)
    {
        return response()->json([Auth::check()]);
    }

}
