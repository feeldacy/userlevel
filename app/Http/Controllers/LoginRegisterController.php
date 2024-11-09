<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginRegisterController extends Controller
{
    public function __construct(){
        $this->middleware('auth')->except(['register', 'login', 'store', 'authenticate']);
    }

    public function register(){
        return view('auth.register');
    }

    public function login(){
        return view('auth.login');
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->withSuccess('You have logged out succesfully!');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users',
            'password' => 'required|min:8|confirmed',
            'level' => ['required', 'string', 'in:user,admin'] ,
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'level' => $request->level,
            'password' => Hash::make($request->password)
        ]);

        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);
        $request->session()->regenerate();
        return redirect()->route('books.index')->withSuccess('You have successfully registeres & logged in!');
    }

    public function authenticate(Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)){
            $request->session()->regenerate();
            return redirect()->route('books.index')->withSuccess('You have succesfully logged in!');
        }

        return back()->withErrors([
            'email' => 'Your provided credentials do not match in out records'
        ])->onlyInput('email');
    }

    public function dashboard(){
        if (Auth::check()){
            return view('book.index');
        }
    }
}