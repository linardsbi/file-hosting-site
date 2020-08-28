<?php

namespace App\Providers;

use App\Observers\FileObserver;
use App\Observers\FolderObserver;
use App\Observers\UserObserver;
use App\User;
use App\Folder;
use App\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Folder::observe(FolderObserver::class);
        File::observe(FileObserver::class);
    }
}
