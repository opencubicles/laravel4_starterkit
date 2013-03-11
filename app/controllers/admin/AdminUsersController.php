<?php

class AdminUsersController extends AdminController {

	/**
	 * Holds some static permissions
	 *
	 * @var array
	 */
	protected $permissions = array(
		'superuser' => 'Super user',
		'admin'     => 'Admin Access'
	);

	/**
	 * Show a list of all the users.
	 *
	 * @return View
	 */
	public function getIndex()
	{
		// Grab all the users
		$users = User::paginate(10);

		// Show the page
		return View::make('admin/users/index', compact('users'));
	}

	/**
	 * User create.
	 *
	 * @return View
	 */
	public function getCreate()
	{
		// Get all the available groups
		$groups = Sentry::getGroupProvider()->findAll();

		// Get all the available permissions
		$permissions = $this->permissions;

		// Selected groups
		$selectedGroups = Input::old('groups', array());

		// Selected permissions
		$selectedPermissions = Input::old('permissions', array());

		// Show the page
		return View::make('admin/users/create', compact('groups', 'permissions', 'selectedGroups', 'selectedPermissions'));
	}

	/**
	 * User create form processing.
	 *
	 * @return Redirect
	 */
	public function postCreate()
	{
		// Declare the rules for the form validation
		$rules = array(
			'first_name'            => 'required|min:3',
			'last_name'             => 'required|min:3',
			'email'                 => 'required|email|unique:users,email',
			'password'              => 'required|between:3,32|confirmed',
			'password_confirmation' => 'required|between:3,32'
		);

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form was validated with success
		if ($validator->passes())
		{
			try
			{
				// Get the inputs, with some exceptions
				$inputs = Input::except('csrf_token', 'password_confirmation', 'groups');

				// Was the user created?
				if ($user = Sentry::getUserProvider()->create($inputs))
				{
					// Assign the selected groups to this user
					foreach (Input::get('groups', array()) as $groupId)
					{
						$group = Sentry::getGroupProvider()->findById($groupId);

						$user->addGroup($group);
					}

					// Redirect to the new user page
					return Redirect::to('admin/users/' . $user->id . '/edit')->with('success', Lang::get('admin/users/messages.create.success'));
				}

				// Redirect to the new user page
				return Redirect::to('admin/users/create')->with('error', Lang::get('admin/users/messages.create.error'));
			}
			catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
			{
				$error = 'login_required';
			}
			catch (Cartalyst\Sentry\Users\PasswordRequiredException $e)
			{
				$error = 'password_required';
			}
			catch (Cartalyst\Sentry\Users\UserExistsException $e)
			{
				$error = 'already_exists';
			}

			// Redirect to the user create page
			return Redirect::to('admin/users/create')->withInput()->with('error', Lang::get('admin/users/messages.' . $error));
		}

		// Form validation failed
		return Redirect::to('admin/users/create')->withInput()->withErrors($validator);
	}

	/**
	 * User update.
	 *
	 * @param  int
	 * @return View
	 */
	public function getEdit($userId = null)
	{
		try
		{
			// Get the user information
			$user = Sentry::getUserProvider()->findById($userId);

			// Get the user groups
			$userGroups = $user->groups()->lists('name', 'group_id');

			// Get all the available groups
			$groups = Sentry::getGroupProvider()->findAll();

			// Get all the available permissions
			$permissions = $this->permissions;

			// Get this user permissions
			$userPermissions = $user->getPermissions();
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			// Redirect to the user management page
			return Redirect::to('admin/users')->with('error', Lang::get('admin/users/messages.does_not_exist'));
		}

		// Show the page
		return View::make('admin/users/edit', compact('user', 'groups', 'userGroups', 'permissions', 'userPermissions'));
	}

	/**
	 * User update form processing page.
	 *
	 * @param  int
	 * @return Redirect
	 */
	public function postEdit($userId = null)
	{
		try
		{
			// Get the user information
			$user = Sentry::getUserProvider()->findById($userId);
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			// Redirect to the user management page
			return Rediret::to('admin/users')->with('error', Lang::get('admin/users/messages.does_not_exist'));
		}

		// Declare the rules for the form validation
		$rules = array(
			'first_name' => 'required|min:3',
			'last_name'  => 'required|min:3',
			'email'      => 'required|email|unique:users,email,' . $user->email . ',email'
		);

		// Do we want to update the user password?
		if (Input::get('password'))
		{
			$rules['password']              = 'required|between:3,32|confirmed';
			$rules['password_confirmation'] = 'required|between:3,32';
		}

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form validates with success
		if ($validator->passes())
		{
			try
			{
				// Update the user
				$user->first_name  = Input::get('first_name');
				$user->last_name   = Input::get('last_name');
				$user->email       = Input::get('email');
				$user->activated   = Input::get('activated', $user->activated);
				$user->permissions = Input::get('permissions');

				// Do we want to update the user password?
				if ($password = Input::get('password'))
				{
					$user->password = $password;
				}

				// Get the current user groups
				$userGroups = $user->groups()->lists('group_id');

				// Get the selected groups
				$selectedGroups = Input::get('groups', array());

				// Groups comparison between the groups the user currently
				// have and the groups the user wish to have.
				$groupsToAdd    = array_diff($selectedGroups, $userGroups);
				$groupsToRemove = array_diff($userGroups, $selectedGroups);

				// Assign the user to groups
				foreach ($groupsToAdd as $groupId)
				{
					$group = Sentry::getGroupProvider()->findById($groupId);

					$user->addGroup($group);
				}

				// Remove the user from groups
				foreach ($groupsToRemove as $groupId)
				{
					$group = Sentry::getGroupProvider()->findById($groupId);

					$user->removeGroup($group);
				}

				// Was the user updated?
				if ($user->save())
				{
					// Redirect to the user page
					return Redirect::to('admin/users/' . $userId . '/edit')->with('success', Lang::get('admin/users/messages.update.success'));
				}
				else
				{
					// Redirect to the user page
					return Redirect::to('admin/users/' . $userId . '/edit')->with('error', Lang::get('admin/users/messages.update.error'));
				}
			}
			catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
			{
				$error = Lang::get('admin/users/messages.login_required');
			}

			// Redirect to the user page
			return Redirect::to('admin/users/' . $userId . '/edit')->withInput()->with('error', $error);
		}

		// Form validation failed
		return Redirect::to('admin/users/' . $userId . '/edit')->withInput()->withErrors($validator);
	}

	/**
	 * Delete the given user, beware that the logged in user
	 * can't be deleted, makes sense, right?
	 *
	 * @param  int  $userId
	 * @return Redirect
	 */
	public function getDelete($userId = null)
	{
		try
		{
			// Get user information
			$user = Sentry::getUserProvider()->findById($userId);

			// Check if we are not trying to delete ourselves
			if ($user->id === Sentry::getId())
			{
				// Redirect to the user management page
				return Redirect::to('admin/users')->with('error', Lang::get('admin/users/messages.delete.impossible'));
			}

			// Was the user deleted?
			if($user->delete())
			{
				// Redirect to the user management page
				return Redirect::to('admin/users')->with('success', Lang::get('admin/users/messages.delete.success'));
			}

			// There was a problem deleting the user
			return Redirect::to('admin/users')->with('error', Lang::get('admin/users/messages.delete.error'));
		}
		catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			// Redirect to the user management page
			return Redirect::to('admin/users')->with('error', Lang::get('admin/users/messages.not_found'));
		}
	}

}
