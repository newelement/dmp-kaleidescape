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
            ],
            [
                'type' => 'boolean',
                'value' => false,
                'field_name' => 'kscape_use_poster',
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
        $this->kscapeSettings['kscape_use_poster'] = (bool) Plugin::getOptionValue('kscape_use_poster');
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
        Plugin::updateOption('kscape_use_poster', $request->boolean('kscape_use_poster'));
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
            $title = str_replace('\\', '', $split[2]);
            // Get meta  data
            // TMDB API
            $playingData = $this->getHighlightedSelection();
            $searchResults = $this->posterSearch($title.' ('.$playingData['year'].')');
            $result = $this->posterMeta($searchResults[0]['id']);
            $result['kscape_poster'] = $playingData['poster'];
            $result['search_title'] = $title.' ('.$playingData['year'].')';
            socket_close($this->socket);
        }
        return $result;
    }

    private function getHighlightedSelection()
    {
        $message = $this->kscapeSettings['kscape_cpdid']."/0/GET_HIGHLIGHTED_SELECTION:\n";
        socket_write($this->socket, $message, strlen($message));
        $result = socket_read($this->socket, 1024);
        $result = str_replace("\n", '', $result);
        $result = str_replace("\r", '', $result);
        // status:HIGHLIGHTED_SELECTION:handle:
        $split = explode(':', $result);
        $handle = $split[2];
        return $this->getContentDetails($handle);
    }

    private function getContentDetails($handle)
    {
        //GET_CONTENT_DETAILS:handle:passcode:
        $passcode = strlen($this->kscapeSettings['kscape_passcode']) ? $this->kscapeSettings['kscape_passcode'] : '';
        $message = $this->kscapeSettings['kscape_cpdid']."/0/GET_CONTENT_DETAILS:$handle:$passcode:\n";
        socket_write($this->socket, $message, strlen($message));
        sleep(2);
        $results = socket_read($this->socket, 1024);
        $linesArr = explode("\n", $results);
        \Log::info($linesArr);
        return $this->getDetails($linesArr);
    }

    private function getDetails($linesArr)
    {
        $arr = [
            'poster' => false,
            'year' => false,
        ];

        foreach ($linesArr as $line) {
            if (strpos($line, ':Year:')) { // 02/0/000:CONTENT_DETAILS:6:Year:2017:
                $splits = explode(':', $line);
                $arr['year'] = $splits[4];
            }
            if (strpos($line, ':HiRes_cover_URL:')) { // 02/0/000:CONTENT_DETAILS:4:HiRes_cover_URL:http\\:\\/\\/192.168.86.23\\/panelcoverarthr\\/ec995295b74d6076\\/40251715.jpg:/68
                $splits = explode(':', $line);
                $arr['poster'] = str_replace('\\', '', $splits[4].':'.$splits[5]);
                ;
            }
        }

        return $arr;
    }

    public function syncMedia()
    {
        return 0;
    }
}
