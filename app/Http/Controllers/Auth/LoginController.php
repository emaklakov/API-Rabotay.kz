<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\HelperChecks;
use App\Helpers\HelperCommons;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Login Controller
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
			'phone' => ['required', 'string', 'min:12', 'max:12', 'phone_number'],
			'firebase_user_uid' => ['required', 'string', 'min:8'],
			'firebase_fcm_token' => ['string', 'min:8'],
		]);
	}

	public function login(Request $request)
	{
		$data = $request->all();

		$validator = $this->validator($data);

		if (!$validator->fails()) {

			$user = User::where('phone', $request->phone)
				//->where('firebase_user_uid', $request->firebase_user_uid)
				//->whereIn('status', [User::ACTIVE, User::OWES])
				//->whereNull('deleted_at')
				->first();

			if (!empty($user)) {
				$this->logoutAPI($user);

				$user->firebase_user_uid = $request->firebase_user_uid;
				$user->deleted_at = null;

				if (array_key_exists('firebase_fcm_token', $data) && !empty($data['firebase_fcm_token'])) {
					$user->secret->firebase_fcm_token = $data['firebase_fcm_token'];
					$user->secret->save();
				}

				$user->save();

				$token = $user->status > 0 ? HelperCommons::createToken($user) : '';
				$response = [
					'access_token' => $token,
					'user' => HelperCommons::getUserData($user->id),
				];

				return response($response, 200);
			} else {
				$user = User::createUser($data);

				$token = HelperCommons::createToken($user);
				$response = [
					'access_token' => $token,
					'user' => HelperCommons::getUserData($user->id),
				];

				return response($response, 201);
			}
		}

		return $this->sendError('Unauthorized', $validator->errors()->all(), 401);
	}

	protected function logoutAPI($user) {
		$user->AauthAcessToken()->delete();
	}
}
