<?php

use Illuminate\Database\Seeder;
use Newelement\DmpKscape\Models\DmpKscapeSetting;

class DmpKscapeSettingsSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DmpKscapeSetting::firstOrCreate(
			[ 'setting_name' => 'enable' ],
			[ 'bool_value' => 0 ]
		);
	}
}
