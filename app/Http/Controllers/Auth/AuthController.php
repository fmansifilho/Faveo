<?php

namespace App\Http\Controllers\Auth;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Common\SettingsController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\LoginRequest;
use App\Http\Requests\helpdesk\RegisterRequest;
use App\User;
// classes
use Auth;
use Hash;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Mail;

/**
 * ---------------------------------------------------
 * AuthController
 * ---------------------------------------------------
 * This controller handles the registration of new users, as well as the
 * authentication of existing users. By default, this controller uses
 * a simple trait to add these behaviors. Why don't you explore it?
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers;
    /* to redirect after login */

    // if auth is agent
    protected $redirectTo = '/dashboard';
    // if auth is user
    protected $redirectToUser = '/profile';
    /* Direct After Logout */
    protected $redirectAfterLogout = '/';
    protected $loginPath = '/auth/login';

    /**
     * Create a new authentication controller instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard     $auth
     * @param \Illuminate\Contracts\Auth\Registrar $registrar
     *
     * @return void
     */
    public function __construct(Guard $auth, Registrar $registrar, PhpMailController $PhpMailController)
    {
        $this->PhpMailController = $PhpMailController;
        SettingsController::smtp();
        $this->auth = $auth;
        $this->registrar = $registrar;
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get the form for registration.
     *
     * @return type Response
     */
    public function getRegister()
    {
        // Event for login
        \Event::fire(new \App\Events\FormRegisterEvent());
        if (Auth::user()) {
            if (Auth::user()->role == 'admin' || Auth::user()->role == 'agent') {
                return \Redirect::route('dashboard');
            } elseif (Auth::user()->role == 'user') {
                // return view('auth.register');
            }
        } else {
            return view('auth.register');
        }
    }

    /**
     * Post registration form.
     *
     * @param type User            $user
     * @param type RegisterRequest $request
     *
     * @return type Response
     */
    public function postRegister(User $user, RegisterRequest $request)
    {
        // Event for login
        \Event::fire(new \App\Events\LoginEvent($request));

        $password = Hash::make($request->input('password'));
        $user->password = $password;
        $name = $request->input('full_name');
        $user->user_name = $name;
        $user->email = $request->input('email');
        // $user->first_name = $request->input('first_name');
        // $user->last_nmae = $request->input('last_nmae');
        // $user->phone_number = $request->input('phone_number');
        // $user->company = $request->input('company');
        $user->role = 'user';
        $code = str_random(60);
        $user->remember_token = $code;
        $user->save();
        // send mail for successful registration
        // $mail = Mail::send('auth.activate', array('link' => url('getmail', $code), 'username' => $name), function ($message) use ($user) {
        // 	$message->to($user->email, $user->full_name)->subject('active your account');
        // });

        $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $name, 'email' => $request->input('email')], $message = ['subject' => 'password', 'scenario' => 'registration-notification'], $template_variables = ['user' => $name, 'email_address' => $request->input('email'), 'password_reset_link' => url('password/reset/'.$code)]);

        return redirect('home')->with('success', 'Activate Your Account ! Click on Link that send to your mail');
    }

    /**
     * Get mail function.
     *
     * @param type      $token
     * @param type User $user
     *
     * @return type Response
     */
    public function getMail($token, User $user)
    {
        $user = $user->where('remember_token', $token)->where('active', 0)->first();
        if ($user) {
            $user->active = 1;
            $user->save();

            return redirect('auth/login');
        } else {
            return redirect('auth/login');
        }
    }

    /**
     * Get login page.
     *
     * @return type Response
     */
    public function getLogin()
    {
        if (Auth::user()) {
            if (Auth::user()->role == 'admin' || Auth::user()->role == 'agent') {
                return \Redirect::route('dashboard');
            } elseif (Auth::user()->role == 'user') {
                return \Redirect::route('home');
            }
        } else {
            return view('auth.login');
        }
    }

    /**
     * Post of login page.
     *
     * @param type LoginRequest $request
     *
     * @return type Response
     */
    public function postLogin(LoginRequest $request)
    {
        // Set login attempts and login time
        $loginAttempts = 1;
        $usernameinput = $request->input('email');
        $password = $request->input('password');
        $field = filter_var($usernameinput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';
        // If session has login attempts, retrieve attempts counter and attempts time
        if (\Session::has('loginAttempts')) {
            $loginAttempts = \Session::get('loginAttempts');
            $loginAttemptTime = \Session::get('loginAttemptTime');
            // $credentials = $request->only('email', 'password');
            $usernameinput = $request->input('email');
            $password = $request->input('password');
            $field = filter_var($usernameinput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';
            // If attempts > 3 and time < 10 minutes
            if ($loginAttempts > 4 && (time() - $loginAttemptTime <= 600)) {
                return redirect()->back()->with('error', 'Maximum login attempts reached. Try again in a while');
            }
            // If time > 10 minutes, reset attempts counter and time in session
            if (time() - $loginAttemptTime > 600) {
                \Session::put('loginAttempts', 1);
                \Session::put('loginAttemptTime', time());
            }
        } else { // If no login attempts stored, init login attempts and time
            \Session::put('loginAttempts', $loginAttempts);
            \Session::put('loginAttemptTime', time());
        }
        // If auth ok, redirect to restricted area
        \Session::put('loginAttempts', $loginAttempts + 1);
        if ($this->auth->attempt([$field => $usernameinput, 'password' => $password], $request->has('remember'))) {
            if (Auth::user()->role == 'user') {
                return \Redirect::route('/');
            } else {
                return redirect()->intended($this->redirectPath());
            }
        }

        return redirect($this->loginPath())
                        ->withInput($request->only('email', 'remember'))
                        ->withErrors([
                            'email'    => $this->getFailedLoginMessage(),
                            'password' => $this->getFailedLoginMessage(),
        ]);
        // Increment login attempts
    }

    /**
     * Get Failed login message.
     *
     * @return type string
     */
    protected function getFailedLoginMessage()
    {
        return 'This Field do not match our records.';
    }
}
