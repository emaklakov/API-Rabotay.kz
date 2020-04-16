<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SubscriptionController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($location_id)
	{
		$authUser = auth()->user();

		$categories = Category::where('is_disabled', false)
			//->defaultOrder()
			->orderBy('count_requests', 'desc')
			->orderBy('sort', 'asc')
			->descendantsOf(1)
			->toTree(1);

		$subscriptions = Subscription::where('user_id', $authUser->id)
			->where('location_id', $location_id)
			->get();

		$subscriptions_categories = [];
		foreach ($subscriptions as $subscription) {
			$subscriptions_categories[$subscription->category_id] = $subscription;
		}

		$subscriptionsTemp = [];

		foreach ($categories as $category) {

			if($category->slug != 'other') {
				$category_icon = !empty($category->icon) ? $category->icon : 'other.png';

				$is_visible_subcategories = false;

				$subcategories = $category->children;

				$subcategoriesTemp = [];

				$subcategoriesTemp[] = [
					'subcategory_id' => $category->id,
					'subcategory_name' => 'Вся категория',
					'subcategory_icon' => URL::to('/images/categories/icons/' . 'subcategory.png'),
					'subscription_id' => isset($subscriptions_categories[$category->id]) ? $subscriptions_categories[$category->id]->id : null
				];

				foreach ($subcategories as $subcategory) {
					if($subcategory->name != 'Другое') {
						//$subcategory_icon = !empty($subcategory->icon) ? $subcategory->icon : 'other.png';
						$subcategory_icon = 'subcategory.png';

						$subcategoriesTemp[] = [
							'subcategory_id' => $subcategory->id,
							'subcategory_name' => $subcategory->name,
							'subcategory_icon' => URL::to('/images/categories/icons/' . $subcategory_icon),
							'subscription_id' => isset($subscriptions_categories[$subcategory->id]) ? $subscriptions_categories[$subcategory->id]->id : null,
						];

						if(isset($subscriptions_categories[$subcategory->id])) {
							$is_visible_subcategories = true;
						}
					}
				}

				$subscriptionsTemp[] = [
					'category_id' => $category->id,
					'category_name' => $category->name,
					'category_icon' => URL::to('/images/categories/icons/' . $category_icon),
					'subscription_id' => isset($subscriptions_categories[$category->id]) ? $subscriptions_categories[$category->id]->id : null,
					'subcategories' => $subcategoriesTemp,
					'is_visible_subcategories' => $is_visible_subcategories
				];
			}
		}

		$response = [
			'subscriptions' => $subscriptionsTemp,
		];

		return response($response, 200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$data = $request->all();

		$authUser = auth()->user();

		$subscription = Subscription::where('user_id', $authUser->id)
			->where('category_id', $data['category_id'])
			->where('location_id', $data['location_id'])
			->first();

		if(empty($subscription)) {
			$subscription = new Subscription();
			$subscription->user_id = $authUser->id;
			$subscription->category_id = $data['category_id'];
			$subscription->location_id = $data['location_id'];
			$subscription->save();
		}

		$response = [
			'id' => $subscription->id
		];

		return response($response, 201);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param \App\Models\Subscription $subscription
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($subscription_id)
	{
		$authUser = auth()->user();

		$subscription = Subscription::where('id', $subscription_id)
			->where('user_id', $authUser->id)
			->firstOrFail();

		$subscription->delete();

		return response([], 200);
	}
}
