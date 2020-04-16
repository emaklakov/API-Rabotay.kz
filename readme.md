php artisan make:model Models/UserInfo -mrc
---

php artisan passport:client --personal

rabotay-personal-ac

Personal access client created successfully.
Client ID: 1
Client secret: d3RCPMj5KGhKgaFNQvIxyDDTuc7ebvNdkwEl7IHX
---

php artisan make:seeder LocationsTableSeeder
---

php artisan db:seed

php artisan db:seed --class=LocationsTableSeeder
php artisan db:seed --class=CategoriesTableSeeder
---
Route::get('/foo', function () {
	$exitCode = Artisan::call('migrate', [
		'--force' => true,
	]);

	//
});
---
composer dump-autoload // it regenerates list of all files and classes which must be included in you application

php artisan queue:work --queue=error --stop-when-empty
