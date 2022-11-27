<?php

namespace Newelement\DmpKscape;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
//use Illuminate\Console\Scheduling\Schedule;
use App\Facades\PluginFacade as Plugin;
use Newelement\DmpKscape\Services\KscapeMediaSyncService;

class DmpKscapeServiceProvider extends ServiceProvider
{
    private $pluginName = 'dmp-kscape';

    public function register()
    {
        $this->app->singleton($this->pluginName, function () {
            return new DmpKscape();
        });

        $this->registerConsoleCommands();
    }

    public function boot(Router $router)
    {
        $viewsDirectory = __DIR__.'/../resources/views/public';
        $publishAssetsDirectory = __DIR__.'/../publishable/assets';

        // Public views
        $this->loadViewsFrom($viewsDirectory, $this->pluginName);
        $this->publishes([$viewsDirectory => base_path('resources/views/vendor/'.$this->pluginName)], 'views');
        $this->publishes([ $publishAssetsDirectory => public_path('vendor/'.$this->pluginName) ], 'public');

        // Register routes
        $router->group([
            'prefix' => 'api',
            'middleware' => ['api']
        ], function ($router) {
            require __DIR__.'/../routes/api.php';
        });

        $router->group([
            'middleware' => ['web']
        ], function ($router) {
            require __DIR__.'/../routes/web.php';
        });

        $this->app->booted(function () {
            // Optional set a command to run on a schedule
            //$schedule = $this->app->make(Schedule::class);
            //$schedule->command('dmp-kscape:sync')->dailyAt('03:00');
        });

        $this->registerPlugin();
        $this->connectToDevice();
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\DmpKscapeSyncCommand::class);
    }

    private function registerPlugin()
    {
        $pluginInfo = [
            'type' => 'media_source',
            'plugin_key' => $this->pluginName,
            'name' => 'Kaleidescape Now Playing',
            'description' => 'Shows now playing.',
            'repo' => 'newelement/dmp-kaleidescape',
            'assets' => [
                'scripts' => ['now_playing' => 'nowplaying.js'], // 'plugin' => 'plugin.js'
                'styles' => 'plugin.css'
            ]
        ];

        Plugin::register($pluginInfo);
    }

    private function connectToDevice()
    {
        $service = new KscapeMediaSyncService();
        $service->tcpConnect();
    }
}
