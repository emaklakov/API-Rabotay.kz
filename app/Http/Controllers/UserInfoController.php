<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class UserInfoController extends Controller
{
	/**
	 * Upload Avatar.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function uploadAvatar(Request $request, $user_id)
	{
		if ($request->hasFile('file')) {
			$file = $request->file('file');

			if ($file->isValid()) {
				$path = public_path() . '/uploads/users/' . $user_id . '/';
				$fileName = 'ava-' . substr(md5($user_id . '-' . time()), 0, 15) . '.' . $file->clientExtension();
				$file->move($path, $fileName);

				$user = User::findOrFail($user_id);

				if (!empty($user->info->avatar_image) && file_exists($path . $user->info->avatar_image)) {
					unlink($path . $user->info->avatar_image);
				}

				$user->info->avatar_image = $fileName;
				$user->info->save();
				$user->save();

				$response = HelperCommons::getUserData($user_id);

				return response($response, 200);
			} else {
				$this->sendError('File isNotValid', null, 400);
			}
		}

		$this->sendError('Not hasFile', null, 400);
	}
}
