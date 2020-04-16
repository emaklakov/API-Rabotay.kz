<?php

namespace App\Models;

use App\Jobs\SendFCM;
use App\Jobs\SendTelegram;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
	const NEW = 0;  // Новое
	const VIEWED  = 10; // Просмотрено
	const HIDE  = 20; // Скрыто

	public static function createNotification($user_id, $title, $description, $icon = '', $action_title = '', $action_route = '', $is_send = true) {
		$user = User::where('id', $user_id)->first();

		if(!empty($user) && $user->status != User::BLOCKED && !empty($user->secret->firebase_fcm_token)) {
			$notification = new Notification();
			$notification->user_id = $user_id;
			$notification->title = $title;
			$notification->description_min = mb_strimwidth($description, 0, 50, "...");
			$notification->description = $description;
			if(!empty($icon)) {
				$notification->icon = $icon;
			}
			if(!empty($action_title)) {
				$notification->action_title = $action_title;
			}
			if(!empty($action_route)) {
				$notification->action_route = $action_route;
			}
			$notification->save();

			if($is_send) {
				//SendFCM::dispatch($notification->id)->onQueue('fcm')->delay(now()->addSecond(5));
				SendFCM::dispatch($notification->id)->onQueue('fcm');
			}
		}
	}

	public static function createTelegramNotification($user_id, $title, $description) {
		$notification = new Notification();
		$notification->user_id = $user_id;
		$notification->title = $title;
		$notification->description_min = 'Telegram';
		$notification->description = $description;
		$notification->status = Notification::HIDE;
		$notification->save();

		SendTelegram::dispatch($notification->id)->onQueue('telegram');
	}
}
