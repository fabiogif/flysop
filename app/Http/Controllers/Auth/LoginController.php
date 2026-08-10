<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    protected $redirectTo = RouteServiceProvider::ADMIN;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated($request, $user)
    {
        activity()
            ->causedBy($user)
            ->withProperties([
                'tenant_id' => $user->tenant_id,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ])
            ->log('auth.login');

        if ($user->isAdmin() || count(array_filter($user->permissions(), fn ($p) => $p !== 'driver.panel')) > 0) {
            return redirect()->intended($this->redirectPath());
        }

        if (in_array('driver.panel', $user->permissions(), true) || $user->driver) {
            return redirect()->route('driver.dashboard');
        }

        return redirect()->intended($this->redirectPath());
    }
}
