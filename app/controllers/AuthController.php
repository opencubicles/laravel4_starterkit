<?php

class AuthController extends BaseController {

	/**
	 * Account login.
	 *
	 * @return View
	 */
	public function getLogin()
	{
		// Are we logged in?
		if (Sentry::check())
		{
			return Redirect::to('');
		}

		// Show the page
		return View::make('site/account/login');
	}

	/**
	 * Account login form processing.
	 *
	 * @return Redirect
	 */
	public function postLogin()
	{
		// Declare the rules for the form validation
		$rules = array(
			'email'    => 'required|email',
			'password' => 'required'
		);

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form validates with success
		if ($validator->passes())
		{
			try
			{
				// Try to log the user in
				if (Sentry::authenticate(Input::only('email', 'password'), Input::get('remember-me', 0)))
				{
					// Get the page we were before
					$redirect = Session::get('loginRedirect', 'account');

					// Unset the page we were before from the session
					Session::forget('loginRedirect');

					// Redirect to the users page
					return Redirect::to($redirect)->with('success', Lang::get('account/auth.messages.login.success'));
				}

				// Redirect to the login page
				return Redirect::to('account/login')->with('error', Lang::get('account/auth.messages.login.error'));
			}
			catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
			{
				$error = 'account_not_found';
			}
			catch (Cartalyst\Sentry\Users\UserNotActivatedException $e)
			{
				$error = 'account_not_activated';
			}
			catch (Cartalyst\Sentry\Throttling\UserSuspendedException $e)
			{
				$error = 'account_suspended';
			}
			catch (Cartalyst\Sentry\Throttling\UserBannedException $e)
			{
				$error = 'account_banned';
			}

			// Redirect to the login page
			return Redirect::to('account/login')->with('error', Lang::get('account/auth.' . $error));
		}

		// Something went wrong
		return Redirect::to('account/login')->withInput()->withErrors($validator);
	}

	/**
	 * User account creation form page.
	 *
	 * @return View
	 */
	public function getRegister()
	{
		// Are we logged in?
		if (Sentry::check())
		{
			return Redirect::to('');
		}

		// Show the page
		return View::make('site/account/register');
	}

	/**
	 * User account creation form processing.
	 *
	 * @return Redirect
	 */
	public function postRegister()
	{
		// Declare the rules for the form validation
		$rules = array(
			'first_name'            => 'required',
			'last_name'             => 'required',
			'email'                 => 'required|email|unique:users',
			'password'              => 'required|confirmed',
			'password_confirmation' => 'required'
		);

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form validates with success
		if ($validator->passes())
		{
			// Create the user and activate the user
			$user = Sentry::register(array(
				'first_name' => Input::get('first_name'),
				'last_name'  => Input::get('last_name'),
				'email'      => Input::get('email'),
				'password'   => Input::get('password')
			));

			// Data to be used on the email view
			$data = array(
				'user' => $user,
                                'activationcode' => $user->getActivationCode()
			);

			// Send the activation code through email
			Mail::send('emails.auth.welcome', $data, function($m) use ($user)
			{
				$m->to($user->email, $user->first_name . ' ' . $user->last_name)->subject('Welcome ' . $user->first_name);
			});

			// Redirect to the register page
			return Redirect::to('account/register')->with('success', 'Account created with success!');
		}

		// Something went wrong
		return Redirect::to('account/register')->withInput()->withErrors($validator);
	}

	/**
	 * User account activation page.
	 *
	 * @param  integer  $userId
	 * @param  string   $actvationCode
	 * @return
	 */
	
	public function getActivate($userId = null, $activationCode = null)
	{
		try
		{
			// Get the user we are trying to activate
			$user = Sentry::getUserProvider()->findById($userId);

			// Try to activate this user account
			if ($user->attemptActivation($activationCode))
			{
				echo 'activated with success';
				
				return Redirect::to('account/login')->with('success', 'Account activated successfully.');
			}

			// The activation failed.
			$error = 'Activation failed.';
		}
                
                catch (Cartalyst\Sentry\Users\UserAlreadyActivatedException $e)
		{
			$error = 'User already active. Please login.';
		}
                
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			$error = 'User does not exist.';
		}

		return Redirect::to('')->with('error', $error);
	}
	

	/**
	 * Forgot password page.
	 *
	 * @return View
	 */
	public function getForgotPassword()
	{
		// Are we logged in?
		if (Sentry::check())
		{
			return Redirect::to('account');
		}

		// Show the page
		return View::make('site/account/forgot-password');
	}

	/**
	 * Forgot password form processing page.
	 *
	 * @return Redirect
	 */
	public function postForgotPassword()
	{
		// Declare the rules for the validator
		$rules = array(
			'email' => 'required|email'
		);

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form validates with success
		if ($validator->passes())
		{
			try
			{
				// Get the user password recovery code
				$user = Sentry::getUserProvider()->findByLogin(Input::get('email'));

				// Data to be used on the email view
				$data = array(
					'user' => $user
				);

				// Send the activation code through email
				Mail::send('emails.auth.forgot-password', $data, function($m) use ($user)
				{
					$m->to($user->email, $user->first_name . ' ' . $user->last_name)->subject('Account Password Recovery');
				});

				// Redirect to the login page
				return Redirect::to('account/forgot-password')->with('success', 'Password recovery email successfully sent.');
			}
			catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
			{
				$error = 'User does not exist.';
			}

			// Redirect to the login page
			return Redirect::to('account/forgot-password')->withInput()->with('error', $error);
		}

		// Something went wrong
		return Redirect::to('account/forgot-password')->withInput()->withErrors($validator);
	}

	/**
	 * Forgot Password Confirmation page.
	 *
	 * @param  string
	 * @return View
	 */
	public function getForgotPasswordConfirmation($userId = null, $resetCode = null)
	{
		try
		{
			//
			$user = Sentry::getUserProvider()->findById($userId);

			//
			if( ! $user->checkResetPasswordCode($resetCode))
			{
				//
				return Redirect::to('account/forgot-password')->with('error', 'Reset password code is invalid.');
			}
		}
		catch(Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			// Redirect to the forgot password page
			return Redirect::to('account/forgot-password')->with('error', 'User does not exist.');
		}

		// Show the page
		return View::make('site/account/forgot-password-confirmation');
	}

	/**
	 * Forgot Password Confirmation form processing page.
	 *
	 * @param  string
	 * @return Redirect
	 */
	public function postForgotPasswordConfirmation($userId = null, $resetCode = null)
	{
		// Declare the rules for the form validation
		$rules = array(
			'password'              => 'required|confirmed',
			'password_confirmation' => 'required'
		);

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form validates with success
		if ($validator->passes())
		{

                  try
                  {
                      // Find the user
                      $user = Sentry::getUserProvider()->findById($userId);

                      // Attempt to reset the user password
                      if ($user->attemptResetPassword($resetCode, Input::get('password')))
                      {
                             return Redirect::to('account/login')->with('success', 'Password changed successfully!');
                      }
                      else
                      {
                          return Redirect::to('account/forgot-password/' . $userId. '/' . $resetCode)->with('error', 'Reset password code is invalid.');
                      }
                  }
                  catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
                  {
                      return Redirect::to('account/forgot-password/' . $userId. '/' . $resetCode)->with('error', 'User does not exist.');
                  }
			
		}

		// Something went wrong
		return Redirect::to('account/forgot-password/' . $userId. '/' . $resetCode)->withInput()->withErrors($validator);
	}

	/**
	 * Logout page.
	 *
	 * @return Redirect
	 */
	public function getLogout()
	{
		// Log the user out
		Sentry::logout();

		// Redirect to the users page
		return Redirect::to('/')->with('success', 'You have successfully logged out!');
	}

}
