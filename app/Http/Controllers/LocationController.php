<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$key = 'regions-v1.0.0';

		$response =  Cache::get($key);
		if($response === null) {
			$regions = Location::where('is_disabled', false)
				->withDepth()
				->defaultOrder()
				->hasParent()
				->get()->toTree();

			$regionsTemp = [];

			foreach ($regions as $region) {
				$regionChildren = $region->children;
				$locations = [];

				foreach ($regionChildren as $children) {
					$locations[] = [
						'id' => $children->id,
						'name' => $children->name,
						'group' => $region->name,
						'group_id' => $region->id
					];
				}

				$locations = collect($locations)->sortBy('name')->values()->all();

				$regionsTemp[] = [
					'id' => $region->id,
					'name' => $region->name,
					'slug' => $region->slug,
					'locations' => $locations
				];
			}

			$response = $regionsTemp;

			Cache::put($key, $response, 10800);
		}

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
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param \App\Models\Location $location
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(Location $location)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Models\Location     $location
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Location $location)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param \App\Models\Location $location
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Location $location)
	{
		//
	}
}
