<?php

namespace Mifra\Crud\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mifra\Crud\MifraCrudServiceProvider;
use Orchestra\Testbench\TestCase;

class CreateCrudCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configura la connessione MongoDB per i test
        Config::set('mifracrud.database', [
            'driver' => env('DB_CONNECTION', 'mongodb'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 27017),
            'database' => env('DB_DATABASE', 'database'),
            'collection' => env('DB_COLLECTION', 'collection'),
            'username' => env('DB_USERNAME', 'username'),
            'password' => env('DB_PASSWORD', 'password'),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [MifraCrudServiceProvider::class];
    }

    /** @test */
    public function it_create()
    {
        $elements =[
            'id' => 5,
            'title' => 'Test CRUD Title',
            'desc' => 'Test CRUD Descrizione',
            'route_name' => 'mifracruds.test',
        ];

        $output = Artisan::call('mifra:createcrud', [
            'elements' => json_encode($elements), // Il comando si aspetta una stringa JSON
        ]);


        // Stampo l'output del comando
        $logs = Artisan::output();
        echo $logs;

        // Verifica che il comando sia eseguito con successo (ritorna 0)
        $this->assertEquals(0, $output);

        // Verifica che i dati siano stati inseriti/aggiornati nel database MongoDB
        $collection = DB::connection('mongodb')->collection(config('mifracrud.database.collection'));
        $insertedItem = $collection->where(['id' => 5])->first();

        $this->assertNotNull($insertedItem);
        $this->assertEquals('Test CRUD Title', $insertedItem['title']);
    }

    protected function tearDown(): void
    {
        // Assicurati di chiamare il tearDown del genitore
        parent::tearDown();
    }

}
