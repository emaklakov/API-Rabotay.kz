<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifySubscribers implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 3;

	protected $category_id;
	protected $location_id;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($category_id, $location_id)
	{
		$this->category_id = $category_id;
		$this->location_id = $location_id;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$subscriptions = Subscription::where('category_id', $this->category_id)
			->where('location_id', $this->location_id)
			->get();

		foreach ($subscriptions as $subscription) {
			Notification::createNotification($subscription->user->id, 'Новая заявка',
											 'В категории, на которую вы подписаны, появились новые заявки.<br><br>Населенный пункт: <strong>'.$subscription->location->name.'</strong><br>Категория: <strong>'.$subscription->category->name.'</strong><br><br>Управлять подписками на категории Вы можете в своих настройках.',
											 '', 'Открыть заявки', '/requests/all/' . $subscription->category->id);
		}
	}
}
