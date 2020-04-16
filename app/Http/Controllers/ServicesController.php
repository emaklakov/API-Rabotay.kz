<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RequestR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicesController extends Controller
{
	public function ratingCategories() {
		$categories = Category::where('is_disabled', false)
			->descendantsOf(1)
			->toTree(1);

		foreach ($categories as $category) {
			$categoriesD = $category->descendants()->pluck('id');
			$categoriesD[] = $category->getKey();
			$count_requests = RequestR::whereIn('status', [RequestR::STATUS_NEW, RequestR::STATUS_CANCELED, RequestR::STATUS_COMPLETED, RequestR::STATUS_ACCEPTED])
				->whereIn('category_id', $categoriesD)->count();
			$category->count_requests = $count_requests;
			$category->save();
		}
		return response([], 200);
	}
}
