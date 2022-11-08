<?php

namespace Newelement\DmpKscape\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Newelement\DmpKscape\Services\KscapeMediaSyncService;

class DmpKscapeController extends Controller
{
	public function install(KscapeMediaSyncService $service)
	{
		return $service->install();
	}

	public function update()
	{
		//
	}

	public function getSettings(KscapeMediaSyncService $service)
	{
		return $service->getSettings();
	}

	public function updateSettings(Request $request, KscapeMediaSyncService $service)
	{
		$service->updateSettings($request);
		return redirect()->back()->with('success', 'Kscape settings updated');
	}

	public function getNowPlaying(KscapeMediaSyncService $service)
	{
		return $service->nowPlaying();
	}
}
