<?php

namespace Mifra\Crud;

use Illuminate\Support\ServiceProvider;

class MifraCrudServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Pubblica il file di configurazione
        $this->publishes([
            __DIR__ . '/../config/mifracrud.php' => config_path('mifracrud.php'),
        ], 'mifracrud');

        // comandi solo per cli
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MifraInstallCrud::class,
            ]);
        }
        // comandi da web
        /* $this->commands([
            Commands\MifraCreateCrud::class
        ]); */
    }

    public function register()
    {
        // Controlla se il service provider di MongoDB è già registrato
        if (!app()->getProvider(\MongoDB\Laravel\MongoDBServiceProvider::class,)) {
            // Registra il ServiceProvider
            //app()->register(\MongoDB\Laravel\MongoDBServiceProvider::class,);
            echo 'Devi installare composer require mongodb/laravel-mongodb';
        }

        // Registra il file di configurazione
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mifracrud.php', 
            'mifracrud'
        );
    }
}
