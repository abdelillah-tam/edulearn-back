<?php

namespace App\Http\Controllers;

use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (Auth::attempt($request->validated())) {

            $request->session()->regenerate();

            $request->session()->save();

            return response()->json(data: Auth::user());
        }

        return response()->json(false);
    }


    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(true);

    }

    public function isSignedIn(Request $request)
    {
        return response()->json(Auth::check());
    }

    public function getUser()
    {
        return response()->json(Auth()->user());
    }

    public function isInstructor()
    {
        if (!Auth::check()) {
            return response()->json(false); // if there is no user logged in, return false
        }

        $user = Auth::user();

        if ($user->type == 'Instructor') {
            return response()->json(true);
        }

        return response()->json(false);
    }

    public function isStudent()
    {
        if (!Auth::check()) {
            return response()->json(false); // if there is no user logged in, return false
        }

        $user = Auth::user();

        if ($user->type == 'Student') {
            return response()->json(true);
        }

        return response()->json(false);
    }

    public function getSetupIntent(Request $request)
    {
        return response()->json([
            'client_secret' => $request->user()->createSetupIntent()->client_secret
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'plan' => 'required|string', // Stripe Price ID (e.g., price_1N...)
        ]);

        $user = $request->user();

        if ($user->subscribed()) {

            return response()->json('You are already subscribed');
        }
        ;

        // Create subscription using Cashier
        $subscription = $user->newSubscription('default', $request->plan)
            ->create($request->payment_method);

        return response()->json(['status' => 'success', 'subscription' => $subscription]);
    }

}
