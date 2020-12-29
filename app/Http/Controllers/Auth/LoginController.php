<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers\Auth;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     * The maximum number of attempts to allow.
     *
     * @var integer
     */
    protected $maxAttempts = 5;

    /**
     * The number of minutes to throttle for.
     *
     * @var integer
     */
    protected $decayMinutes = 2;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('ajax')->only('refreshCaptcha');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if($user->active !== 1){
            $this->guard()->logout();
            $request->session()->invalidate();

            return $this->sendDisabledResponse();
        }

        $this->saveTimezone($request);

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Save timezone value to session manager
     *
     * @param  Request $request
     * @return void
     */
    protected function saveTimezone(Request $request)
    {
        session([User::TIMEZONE_SESSION_KEY => $request->timezone]);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|captcha',
            'timezone' => 'required',
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws ValidationException
     */
    protected function sendDisabledResponse()
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.disabled')],
        ]);
    }

    /**
     * Refresh captcha images.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws NotFoundHttpException
     */
    public function refreshCaptcha(Request $request)
    {
        return response()->json(['source' => captcha_src()], 200);
    }
}
