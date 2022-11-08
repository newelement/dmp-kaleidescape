<?php

namespace Newelement\DmpKscape\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use App\Interfaces\MediaSyncInterface;
use App\Traits\PosterProcess;
use Illuminate\Support\Facades\Artisan;
use Plugin;

class KscapeMediaSyncService implements MediaSyncInterface
{
	use PosterProcess;

	public $kscapeSettings = [];

	public function __construct()
	{
		$this->setSettings();
		$this->setKscapeSettings();
	}

	public function setSettings()
	{
		$this->settings = Setting::first();
	}

	/**
	 * Install the plugin
	 *
	 * @return array
	 */
	public function install(): array
	{
		Artisan::call('vendor:publish', ['--provider' => 'Newelement\DmpKscape\DmpKscapeServiceProvider', '--force' => true]);

		$plugin = [
			'type' => 'media_source',
			'plugin_key' => 'dmp-kscape',
			'name' => 'Kaleidescape Media Sync and Now Playing',
			'description' => 'Syncs movie posters and shows now playing.',
			'url' => 'https://github.com/newelement/dmp-kaleidescape',
			'repo' => 'newelement/dmp-kaleidescape',
			'version' => '1.0.0',
		];

		Plugin::install($plugin);

		$options = [
			[
				'type' => 'string',
				'value' => 'localhost',
				'field_name' => 'kscape_url',
				'plugin_key' => 'dmp-kscape',
			],
			[
				'type' => 'string',
				'value' => '8989',
				'field_name' => 'kscape_port',
				'plugin_key' => 'dmp-kscape',
			],
			[
				'type' => 'string',
				'value' => '9090',
				'field_name' => 'kscape_socket_port',
				'plugin_key' => 'dmp-kscape',
			],
			[
				'type' => 'string',
				'value' => null,
				'field_name' => 'kscape_username',
				'plugin_key' => 'dmp-kscape',
			],
			[
				'type' => 'string',
				'value' => null,
				'field_name' => 'kscape_password',
				'plugin_key' => 'dmp-kscape',
				'secret' => true
			]
		];

		Plugin::addOptions($options);

		Artisan::call('optimize:clear');
		Artisan::call('optimize');

		return ['success' => true];
	}

	public function update()
	{
		//
	}

	public function setKscapeSettings()
	{
		// Can also call Plugin::getOptions('dmp-kscape') to get full options array
		$this->kscapeSettings['kscape_url'] = Plugin::getOptionValue('kscape_url');
		$this->kscapeSettings['kscape_port'] = Plugin::getOptionValue('kscape_port');
		$this->kscapeSettings['kscape_socket_port'] = Plugin::getOptionValue('kscape_socket_port');
		$this->kscapeSettings['kscape_username'] = Plugin::getOptionValue('kscape_username');
		$this->kscapeSettings['kscape_password'] = Plugin::getOptionValue('kscape_password');
	}

	public function getSettings()
	{
		return $this->kscapeSettings;
	}

	public function updateSettings($request)
	{
		Plugin::updateOption('kscape_url', $request->kscape_url);
		Plugin::updateOption('kscape_port', $request->kscape_port);
		Plugin::updateOption('kscape_socket_port', $request->kscape_socket_port);
		Plugin::updateOption('kscape_username', $request->kscape_username);
		Plugin::updateOption('kscape_password', $request->kscape_password);
	}

	/**
	 * Make API calls to media server
	 *
	 * @param string $path /path/resource
	 * @param string $method get|post
	 * @param array $params
	 *
	 * @return json
	 */
	public function apiCall($jsonRpc, $method = 'GET', $params = [])
	{
		$request = 'http://'.$this->kscapeSettings['kscape_url'].':'.$this->kscapeSettings['kscape_port'].'/jsonrpc?request='.$jsonRpc;

		if (strlen($this->kscapeSettings['kscape_username']) && strlen($this->kscapeSettings['kscape_password'])) {
			$response = Http::withBasicAuth(
				$this->kscapeSettings['kscape_username'],
				$this->kscapeSettings['kscape_password']
			)
				->get($request);
		} else {
			$response = Http::get($request);
		}

		return $response->json();
	}

	public function syncMedia($page = 0)
	{
		$limit = 20;
		$start = $page * $limit;
		$end = $limit * ($page+1);

		$jsonRpc = '{"jsonrpc": "2.0", "method": "VideoLibrary.GetMovies", "params": {"limits": { "start" : '.$start.', "end": '.$end.' }, "properties" : ["art", "rating", "mpaa", "runtime"], "sort": { "order": "ascending", "method": "label", "ignorearticle": true } }, "id": "libMovies"}';

		$json = $this->apiCall($jsonRpc);

		if (isset($json['result']) && isset($json['result']['movies'])) {
			if (count($json['result']['movies']) > 0) {
				$movies = $json['result']['movies'];
				$this->processMovies($movies);

				if ($end < $json['result']['limits']['total']) {
					$page = $page+1;
					$this->syncMedia($page);
				}
			}
		}

		return ['success' => true];
	}

	public function processMovies($movies)
	{
		foreach ($movies as $movie) {
			if (isset($movie['art']) && isset($movie['art']['poster']) && $movie['art']['poster']) {
				$imageUrl = urldecode(str_replace('image://', '', rtrim($movie['art']['poster'], '/')));

				$savedImage = $this->saveImage($movie['label'], $imageUrl);

				$params = [
					'media_type' => 'movie',
					'name' => $movie['label'],
					'file_name' => $savedImage['file_name'],
					'id' => 'kscape-'.$movie['movieid'],
					'rating' => isset($movie['mpaa']) ? str_replace('Rated ', '', $movie['mpaa']) : null,
					'audience_rating' => isset($movie['rating']) ? $movie['rating'] : 0,
					'runtime' => is_numeric($movie['runtime']) ? $movie['runtime'] / 60 : null
				];

				$this->savePoster($params);
			}
		}
	}

	public function nowPlaying()
	{
		$jsonRpc = '[{"jsonrpc":"2.0","method":"Player.GetItem","params":{"properties":["title","rating","mpaa","runtime"],"playerid":1},"id":"VideoGetItem"},{"jsonrpc":"2.0","id":1,"method":"Player.GetItem","params":{"playerid":1,"properties":["art"]}}]';

		$json = $this->apiCAll($jsonRpc);

		return $json;
	}

	private function syncTv($sections)
	{
		//
	}
}
