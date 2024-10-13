<?php
declare(strict_types=1);
namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard.users');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard.users')->with('res', [
                'method' => 'success',
                'msg' => 'Добро пожаловать'
            ]);
        }
        try {
            if(!$this->loginFunction($request)){
                return redirect()->route('login')->with('res', [
                    'method' => 'error',
                    'msg' => trans('messages.auth.failed')
                ]);
            }
            return to_route('dashboard.index')->with('res', [
                'method' => 'success',
                'msg' => trans('Добро пожаловать')
            ]);
        } catch (Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        return to_route('login')->with('res', [
            'method' => 'success',
            'msg' => trans('messages.auth.logout')
        ]);
    }

    public function loginFunction (Request $request):bool
    {
        $user = User::query()->where('name', $request->get('name'))->first();
        if (is_null($user) || !Hash::check($request->get('password'), $user->password)) {
            return false;
        }
        Auth::login($user);
        return true;
    }
}
