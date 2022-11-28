<?php

namespace Newelement\DmpKscape\Services;

use App\Models\Setting;
use App\Interfaces\MediaSyncInterface;
use App\Traits\PosterProcess;
use Illuminate\Support\Facades\Artisan;
use Plugin;

class KscapeMediaSyncService implements MediaSyncInterface
{
    use PosterProcess;

    public $kscapeSettings = [];
    private $socket;

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
            'name' => 'Kaleidescape Now Playing',
            'description' => 'Shows now playing.',
            'url' => 'https://github.com/newelement/dmp-kaleidescape',
            'repo' => 'newelement/dmp-kaleidescape',
            'version' => '1.0.0',
        ];

        Plugin::install($plugin);

        $options = [
            [
                'type' => 'string',
                'value' => '192.0.0.0',
                'field_name' => 'kscape_ip_address',
                'plugin_key' => 'dmp-kscape',
            ],
            [
                'type' => 'string',
                'value' => '10000',
                'field_name' => 'kscape_port',
                'plugin_key' => 'dmp-kscape',
            ],
            [
                'type' => 'string',
                'value' => '01',
                'field_name' => 'kscape_cpdid',
                'plugin_key' => 'dmp-kscape',
            ],
            [
                'type' => 'string',
                'value' => null,
                'field_name' => 'kscape_passcode',
                'plugin_key' => 'dmp-kscape',
            ],
            [
                'type' => 'boolean',
                'value' => false,
                'field_name' => 'kscape_use_ssl',
                'plugin_key' => 'dmp-kscape',
            ]
        ];

        Plugin::addOptions($options);

        return ['success' => true];
    }

    public function update()
    {
        //
    }

    public function setKscapeSettings()
    {
        // Can also call Plugin::getOptions('dmp-kscape') to get full options array
        $this->kscapeSettings['kscape_ip_address'] = Plugin::getOptionValue('kscape_ip_address');
        $this->kscapeSettings['kscape_port'] = Plugin::getOptionValue('kscape_port');
        $this->kscapeSettings['kscape_cpdid'] = Plugin::getOptionValue('kscape_cpdid');
        $this->kscapeSettings['kscape_passcode'] = Plugin::getOptionValue('kscape_passcode');
        $this->kscapeSettings['kscape_use_ssl'] = Plugin::getOptionValue('kscape_use_ssl');
    }

    public function getSettings()
    {
        return $this->kscapeSettings;
    }

    public function updateSettings($request)
    {
        Plugin::updateOption('kscape_ip_address', $request->kscape_ip_address);
        Plugin::updateOption('kscape_port', $request->kscape_port);
        Plugin::updateOption('kscape_cpdid', $request->kscape_cpdid);
        Plugin::updateOption('kscape_passcode', $request->kscape_passcode);
        //Plugin::updateOption('kscape_use_ssl', $request->kscape_use_ssl);
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
        // NA
    }

    public function tcpConnect()
    {
        $host = $this->kscapeSettings['kscape_ip_address'];
        $port = $this->kscapeSettings['kscape_port'];
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        return socket_connect($this->socket, $host, $port);
    }

    public function getStatus()
    {
        $status = 'stopped';
        $connected = $this->tcpConnect();
        if ($connected) {
            $message = $this->kscapeSettings['kscape_cpdid']."/0/GET_PLAY_STATUS:\n";
            socket_write($this->socket, $message, strlen($message));
            $result = socket_read($this->socket, 1024);
            $result = str_replace("\n", '', $result);
            $result = str_replace("\r", '', $result);
            // 02/0/000:PLAY_STATUS:2:0:01:09385:02651:010:00117:00013:/52
            $split = explode(':', $result);
            $code = (int) $split[2];
            $status = $code === 2 ? 'playing' : 'stopped';
            socket_close($this->socket);
        }

        return $status;
    }

    public function nowPlaying()
    {
        $result = false;
        $connected = $this->tcpConnect();
        if ($connected) {
            $message = $this->kscapeSettings['kscape_cpdid']."/0/GET_PLAYING_TITLE_NAME:\n";
            socket_write($this->socket, $message, strlen($message));
            $result = socket_read($this->socket, 1024);
            $result = str_replace("\n", '', $result);
            $result = str_replace("\r", '', $result);
            //  02/0/000:TITLE_NAME:West Side Story:/92
            $split = explode(':', $result);
            $title = $split[2];
            // Get meta  data
            // TMDB API
            $searchResults = $this->posterSearch($title);
            $result = $this->posterMeta($searchResults[0]['id']);
            socket_close($this->socket);
        }
        return $result;
    }

    public function syncMedia()
    {
        return 0;
    }
}
