<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$authUser = auth()->user();

		$per_page = 10;

		$data = $request->all();

		$sort = 'desc';

		$notifications = Notification::where('user_id', $authUser->id)
			->whereIn('status', [Notification::NEW, Notification::VIEWED])
			->orderBy('created_at', $sort)
			->paginate($per_page);

		$lastPage = $notifications->lastPage();
		$currentPage = $notifications->currentPage();
		$nextPage = $currentPage + 1;

		$notificationsTemp = [];

		foreach ($notifications as $notification) {
			$notificationsTemp[] = [
				'id' => $notification->id,
				'title' => $notification->title,
				'description_min' => $notification->description_min,
				'icon' => $notification->icon,
				'status' => $notification->status,
				'created_at' => HelperCommons::getValidFormatDate($notification->created_at),
			];
		}

		$new_notifications_exists = Notification::where('user_id', $authUser->id)
			->where('status', Notification::NEW)
			->exists();

		$response = [
			'notifications' => $notificationsTemp,
			'next_page' => $nextPage,
			'current_page' => $currentPage,
			'last_page' => $lastPage,
			'new_notifications_exists' => $new_notifications_exists,
		];

		return response($response, 200);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param \App\Models\Notification $notification
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($notification_id)
	{
		$authUser = auth()->user();

		$notification = Notification::where('id', $notification_id)
			->where('user_id', $authUser->id)
			->firstOrFail();

		$isUpdate = false;
		if($notification->status == 0) {
			$notification->status = Notification::VIEWED;
			$notification->icon = $notification->icon == 'notifications' ? 'notifications-outline' : $notification->icon;
			$notification->save();
			$isUpdate = true;
		}

		$response = [
			'id' => $notification->id,
			'title' => $notification->title,
			'description' => '<div class="pb-3">'.$notification->description.'</div>',
			'icon' => $notification->icon,
			'status' => $notification->status,
			'action_title' => $notification->action_title,
			'action_route' => $notification->action_route,
			'created_at' => HelperCommons::getValidFormatDate($notification->created_at),
			'is_update' => $isUpdate
		];

		return response($response, 200);
	}

	public function newExists() {
		$authUser = auth()->user();

		$new_notifications_exists = Notification::where('user_id', $authUser->id)
			->where('status', Notification::NEW)
			->exists();

		$response = [
			'new_notifications_exists' => $new_notifications_exists,
		];

		return response($response, 200);
	}

	public function received($notification_id)
	{
		$notification = Notification::where('id', $notification_id)
			->firstOrFail();

		$notification->is_received = true;
		$notification->save();

		$response = [
			'id' => $notification->id
		];

		return response($response, 200);
	}

	public function store(Request $request)
	{
		$data = $request->all();

		if(!empty($data)) {
			if(isset($data['apiKey']) && $data['apiKey'] == '9ccbf283-7c50-4a47-bdc8-17d24d58d3a0') {
				$user_id = $data['user_id'];
				$title = $data['title'];
				$description = $data['description'];
				$action_title = $data['action_title'];
				$action_route = $data['action_route'];
				$icon = $data['icon'];

				Notification::createNotification($user_id, $title,
												 $description,
												 $icon, $action_title, $action_route);

				return response([], 200);
			}
		}

		return response([], 400);
	}

	public function storeAll(Request $request)
	{
		$data = $request->all();

		if(!empty($data)) {
			if(isset($data['apiKey']) && $data['apiKey'] == '9ccbf283-7c50-4a47-bdc8-17d24d58d3a0') {
				$title = $data['title'];
				$description = $data['description'];
				$action_title = $data['action_title'];
				$action_route = $data['action_route'];
				$icon = $data['icon'];

				$users = User::all();

				foreach ($users as $user) {
					Notification::createNotification($user->id, $title,
													 $description,
													 $icon, $action_title, $action_route, false);
				}

				return response([], 200);
			}
		}

		return response([], 400);
	}
}
