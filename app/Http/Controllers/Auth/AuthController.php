<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\User;
/* include User Model */
use Hash;
/* Include RegisterRequest */
use Illuminate\Contracts\Auth\Guard;
/* Register validation */
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
/* Include login validator */
use Mail;
use App\Http\Controllers\SettingsController;
use Auth;

/**
 * ---------------------------------------------------
 * AuthController
 * ---------------------------------------------------
 * This controller handles the registration of new users, as well as the
 * authentication of existing users. By default, this controller uses
 * a simple trait to add these behaviors. Why don't you explore it?
 *
 * @package Controllers
 * @subpackage Controller
 * @author      Ladybird <info@ladybirdweb.com>
 */
class AuthController extends Controller {

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
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar) {
		SettingsController::smtp();
		$this->auth = $auth;
		$this->registrar = $registrar;
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	/**
	 * Get the form for registration
	 * @return type Response
	 */
	public function getRegister() {
		if(Auth::user()) {
			if(Auth::user()->role == "admin" || Auth::user()->role == "agent"){
				return \Redirect::route('dashboard');	
			}
			elseif(Auth::user()->role == "user"){
				// return view('auth.register');	
			}
		}
		else{
			return view('auth.register');	
		}
		
	}

	/**
	 * Post registration form
	 * @param type User $user
	 * @param type RegisterRequest $request
	 * @return type Response
	 */
	public function postRegister(User $user, RegisterRequest $request) {
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
		$mail = Mail::send('auth.activate', array('link' => url('getmail', $code), 'username' => $name), function ($message) use ($user) {
			$message->to($user->email, $user->full_name)->subject('active your account');
		});
		return redirect('home')->with('success', 'Activate Your Account ! Click on Link that send to your mail');
	}

	/**
	 * Get mail function
	 * @param type $token
	 * @param type User $user
	 * @return type Response
	 */
	public function getMail($token, User $user) {
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
	 * Get login page
	 * @return type Response
	 */
	public function getLogin() {
		if(Auth::user()) {
			if(Auth::user()->role == "admin" || Auth::user()->role == "agent"){
				return \Redirect::route('dashboard');	
			}
			elseif(Auth::user()->role == "user"){
				return \Redirect::route('home');		
			}
		}
		else{
			return view('auth.login');
		}	
	}

	/**
	 * Post of login page
	 * @param type LoginRequest $request
	 * @return type Response
	 */
	public function postLogin(LoginRequest $request) {
		// $email = $request->input('email');
		// $password = Hash::make($request->input('password'));
		// $remember = $request->input('remember');
		// dd([$email,$password,$remember]);
		$credentials = $request->only('email', 'password');
		if ($this->auth->attempt($credentials, $request->has('remember'))) {
			if(Auth::user()->role == 'user')
			{
				return \Redirect::route('home');
			}
			else
			{
				return redirect()->intended($this->redirectPath());
			}
		}
		return redirect($this->loginPath())
			->withInput($request->only('email', 'remember'))
			->withErrors([
				'email' => $this->getFailedLoginMessage(),
				'password' => $this->getFailedLoginMessage(),
			]);
	}

	/**
	 * Get Failed login message
	 * @return type string
	 */
	protected function getFailedLoginMessage() {
		return 'This Field do not match our records.';
	}
}
