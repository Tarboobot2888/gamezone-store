<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class AuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.failed')
        ], 401);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard')
        ]);
    }

    public function firebaseAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->idToken);
            $firebaseUser = $this->firebaseAuth->getUser($verifiedIdToken->claims()->get('sub'));

            $user = User::where('firebase_uid', $firebaseUser->uid)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $firebaseUser->displayName ?? 'User',
                    'email' => $firebaseUser->email,
                    'firebase_uid' => $firebaseUser->uid,
                    'avatar' => $firebaseUser->photoUrl,
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]);
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect' => route('dashboard')
            ]);

        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token'
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
