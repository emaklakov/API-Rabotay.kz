<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\HelperChecks;
use App\Helpers\HelperCommons;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param array $data
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function validator(array $data)
	{
		Validator::extend('phone_number', function($attribute, $value, $parameters) {
			return HelperChecks::isPhoneNumber($value);
		});

		Validator::replacer('phone_number', function($message, $attribute, $rule, $parameters) {
			return str_replace(':attribute', $attribute, ':attribute is invalid phone number. Correct format +77000000000');
		});

		return Validator::make($data, [
			'first_name' => ['required', 'string', 'max:255'],
			'phone' => ['required', 'string', 'min:12', 'max:12', 'phone_number', 'unique:users'],
			'firebase_user_uid' => ['required', 'string', 'min:8'],
		]);
	}

	/**
	 * Registration new user
	 *
	 * @param array $data
	 *
	 * @return \App\Models\User
	 */
	public function registration(Request $request)
	{
		$validator = $this->validator($request->all());

		if(!$validator->fails()) {
			$user = User::createUser($request->all());

			$token = HelperCommons::createToken($user);
			$response = [
				'access_token' => $token,
				'user' => [
					'id' => $user->id,
					'first_name' => $user->first_name,
					'last_name' => $user->last_name,
					'middle_name' => $user->middle_name,
					'phone' => $user->phone,
					'location_id' => $user->info->location_id,
					'location_name' => '',
					'avatar_image' => $user->info->avatar_image,
					'status' => $user->status
				],
			];

			return response($response, 201);
		}

		return $this->sendError('Bad Request', $validator->errors(), 400);
	}
}
