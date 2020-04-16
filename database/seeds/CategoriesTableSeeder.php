<?php

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$categoryRoot = Category::where('slug', 'all')->first();

		$category = new Category([
									 'name' => 'Бухгалтерская помощь',
									 'slug' => 'accountingassistance',
									 'is_disabled' => false,
									 ]
		);
		if ($category->save()) {
			$moved = $category->hasMoved();
		}

		$category->appendToNode($categoryRoot)->save();
		$category->children()->create([
										   'name' => 'Другое',
										   'slug' => 'accountingassistanceother',
										   'is_disabled' => false,
									   ]
		);

		/*
		if(!Category::where('slug', 'all')->exists()){
			$categoryRoot = new Category([
										 'name' => 'Все категории',
										 'slug' => 'all',
										 'is_disabled' => false,
									 ]
			);
			if ($categoryRoot->save()) {
				$moved = $categoryRoot->hasMoved();
			}
		}

		// Добавить категорию

		$category2 = Category::findOrFail(2);
		$category2->children()->create([
										'name' => 'Другое',
										'slug' => 'healthandbeautyother',
										'is_disabled' => false,
									]
		);
		$category3 = Category::findOrFail(3);
		$category3->children()->create([
										  'name' => 'Другое',
										  'slug' => 'teachingother',
										  'is_disabled' => false,
									  ]
		);
		$category4 = Category::findOrFail(4);
		$category4->children()->create([
										  'name' => 'Другое',
										  'slug' => 'taxiother',
										  'is_disabled' => false,
									  ]
		);
		$category5 = Category::findOrFail(5);
		$category5->children()->create([
										  'name' => 'Другое',
										  'slug' => 'autoother',
										  'is_disabled' => false,
									  ]
		);
		$category6 = Category::findOrFail(6);
		$category6->children()->create([
										  'name' => 'Другое',
										  'slug' => 'legaladviceother',
										  'is_disabled' => false,
									  ]
		);
		$category7 = Category::findOrFail(7);
		$category7->children()->create([
										  'name' => 'Другое',
										  'slug' => 'electronicrepairother',
										  'is_disabled' => false,
									  ]
		);
		$category8 = Category::findOrFail(8);
		$category8->children()->create([
										  'name' => 'Другое',
										  'slug' => 'techrepairother',
										  'is_disabled' => false,
									  ]
		);
		$category9 = Category::findOrFail(9);
		$category9->children()->create([
										  'name' => 'Другое',
										  'slug' => 'photoshopother',
										  'is_disabled' => false,
									  ]
		);
		$category10 = Category::findOrFail(10);
		$category10->children()->create([
										  'name' => 'Другое',
										  'slug' => 'webdevelopmentother',
										  'is_disabled' => false,
									  ]
		);
		$category11 = Category::findOrFail(11);
		$category11->children()->create([
										  'name' => 'Другое',
										  'slug' => 'designother',
										  'is_disabled' => false,
									  ]
		);
		$category12 = Category::findOrFail(12);
		$category12->children()->create([
										  'name' => 'Другое',
										  'slug' => 'promoother',
										  'is_disabled' => false,
									  ]
		);
		$category13 = Category::findOrFail(13);
		$category13->children()->create([
										  'name' => 'Другое',
										  'slug' => 'computerhelpother',
										  'is_disabled' => false,
									  ]
		);
		$category14 = Category::findOrFail(14);
		$category14->children()->create([
										  'name' => 'Другое',
										  'slug' => 'virtualassistantother',
										  'is_disabled' => false,
									  ]
		);
		$category15 = Category::findOrFail(15);
		$category15->children()->create([
										  'name' => 'Другое',
										  'slug' => 'houseother',
										  'is_disabled' => false,
									  ]
		);
		$category16 = Category::findOrFail(16);
		$category16->children()->create([
										  'name' => 'Другое',
										  'slug' => 'truckingother',
										  'is_disabled' => false,
									  ]
		);
		$category17 = Category::findOrFail(17);
		$category17->children()->create([
										  'name' => 'Другое',
										  'slug' => 'confectionerother',
										  'is_disabled' => false,
									  ]
		);
		$category18 = Category::findOrFail(18);
		$category18->children()->create([
										  'name' => 'Другое',
										  'slug' => 'clericalother',
										  'is_disabled' => false,
									  ]
		);
		$category19 = Category::findOrFail(19);
		$category19->children()->create([
										  'name' => 'Другое',
										  'slug' => 'courierother',
										  'is_disabled' => false,
									  ]
		);
		$category20 = Category::findOrFail(20);
		$category20->children()->create([
										  'name' => 'Все',
										  'slug' => 'allother',
										  'is_disabled' => false,
									  ]
		);
		*/
	}
}
