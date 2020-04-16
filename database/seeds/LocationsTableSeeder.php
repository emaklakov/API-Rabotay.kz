<?php

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		/*
		$country = Location::findOrFail(1);

		$region = new Location([
								   'name' => 'Северо-Казахстанская обл.',
								   'slug' => 'sko',
								   'is_disabled' => false,
							   ]
		);
		if ($region->save()) {
			$moved = $region->hasMoved();
		}

		$region->appendToNode($country)->save();

		$region->children()->create([
										'name' => 'Петропавловск',
										'slug' => 'petropavlovsk',
										'is_disabled' => false,
									]
		);
		*/
		// Добавить регион
		/*
		$country = Location::findOrFail(1);

		$region = new Location([
								   'name' => 'Алматинская обл.',
								   'slug' => 'alo',
								   'is_disabled' => false,
							   ]
		);
		if ($region->save()) {
			$moved = $region->hasMoved();
		}

		$region->appendToNode($country)->save();
		*/

		/*
		// Добавить населенный пункт

		$region = Location::findOrFail(2);

		$region->children()->create([
										'name' => 'Капчагай',
										'slug' => 'kapchagay',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Каскелен',
										'slug' => 'kaskelen',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Сарканд',
										'slug' => 'sarkand',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Талдыкорган',
										'slug' => 'taldykorgan',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Текели',
										'slug' => 'tekeli',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Ушарал',
										'slug' => 'usharal',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Уштобе',
										'slug' => 'ushtobe',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Жаркент',
										'slug' => 'zharkent',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Шелек',
										'slug' => 'shelek',
										'is_disabled' => false,
									]
		);
		*/

		/*
		$country = new Location([
								   'name' => 'Казахстан',
								   'slug' => 'kazakhstan',
								   'is_disabled' => false,
							   ]
		);
		if ($country->save()) {
			$moved = $country->hasMoved();
		}

		$region = new Location([
								   'name' => 'Алматинская обл.',
								   'slug' => 'alo',
								   'is_disabled' => false,
							   ]
		);
		if ($region->save()) {
			$moved = $region->hasMoved();
		}

		$region->appendToNode($country)->save();

		$region->children()->create([
										'name' => 'Алматы',
										'slug' => 'almaty',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Талгар',
										'slug' => 'talgar',
										'is_disabled' => false,
									]
		);

		$region->children()->create([
										'name' => 'Есик',
										'slug' => 'esik',
										'is_disabled' => false,
									]
		);
		*/


	}
}
