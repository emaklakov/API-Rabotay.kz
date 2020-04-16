<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CategoryController extends Controller
{
	public function index()
	{
		$nodes = Category::where('is_disabled', false)
			//->withDepth()
			//->defaultOrder()
			->descendantsOf(1)
			//->get()
			->toTree(1);

		$categoriesTemp = $this->traverse($nodes);

		$response = [
			'categories' => $categoriesTemp,
		];

		return response($response, 200);
	}

	private function traverse($categories, $prefix = '')
	{
		$categoriesTemp = [];

		foreach ($categories as $category) {
			$icon = !empty($category->icon) ? $category->icon : 'other.png';
			$parent = $category->parent;

			$categoriesTemp[] = [
				'id' => $category->id,
				'name' => trim($prefix . ' ' . $category->name),
				'slug' => $category->slug,
				'icon' => URL::to('/images/categories/icons/' . $icon),
				'sort' => $category->sort,
				'parent_name' => $parent->slug != 'all' ? $parent->name : '',
			];

			$categoriesTraverse = []; //$this->traverse($category->children, $prefix.'-');

			$categoriesTemp = array_merge($categoriesTemp, $categoriesTraverse);
		}

		return $categoriesTemp;
	}

	public function children($category_id = 1)
	{
		$categories = Category::where('is_disabled', false)
			//->withDepth()
			//->defaultOrder()
			->orderBy('count_requests', 'desc')
			->orderBy('sort', 'asc')
			->descendantsOf($category_id)
			->toTree($category_id);

		$title = '';

		if ($category_id > 0) {
			$category = Category::where('is_disabled', false)
				->where('id', $category_id)->first();

			if ($category) {
				$title = $category->name;
			} else {
				$title = 'Подкатегории';
			}
		}

		$categoriesTemp = [];
		foreach ($categories as $category) {
			$icon = !empty($category->icon) ? $category->icon : 'other.png';

			$categoriesTemp[] = [
				'id' => $category->id,
				'name' => $category->name,
				'slug' => $category->slug,
				'icon' => URL::to('/images/categories/icons/' . $icon),
				'sort' => $category->sort,
			];
		}

		$response = [
			'title' => $title,
			'categories' => $categoriesTemp,
		];

		return response($response, 200);
	}

	public function top()
	{
		$key = 'top-category-v1.0.0';

		$response =  Cache::get($key);
		if($response === null) {
			$categories = Category::where('is_disabled', false)
				->orderBy('count_requests', 'desc')
				->orderBy('sort', 'asc')
				->take(5)
				->descendantsOf(1)
				->toTree(1);

			$title = 'Популярные категории';

			$categoriesTemp = [];
			foreach ($categories as $category) {
				$icon = !empty($category->icon) ? $category->icon : 'other.png';

				$categoriesTemp[] = [
					'id' => $category->id,
					'name' => $category->name,
					'slug' => $category->slug,
					'icon' => URL::to('/images/categories/icons/' . $icon),
					'sort' => $category->sort,
				];
			}

			$response = [
				'title' => $title,
				'categories' => $categoriesTemp,
				'card_img' => URL::to('/images/home/card/1.jpeg'),
				'home_content' => ''
			];

			Cache::put($key, $response, 3600);
		}

		return response($response, 200);
	}

	public function show($category_id)
	{
		$category = Category::where('is_disabled', false)
			->where('id', $category_id)->firstOrFail();

		$parent = $category->parent;

		$categoryName = $category->name;

		if (!empty($parent) && $category->slug != 'other') {
			if (stripos($category->slug, 'other')) {
				$categoryName = $parent->name;
			}
		}

		$response = [
			'id' => $category->id,
			'name' => $categoryName,
			'slug' => $category->slug,
			'sort' => $category->sort,
			'parent_id' => !empty($parent) ? $parent->id : '',
			'parent_name' => !empty($parent) ? $parent->name : '',
		];

		return response($response, 200);
	}
}
