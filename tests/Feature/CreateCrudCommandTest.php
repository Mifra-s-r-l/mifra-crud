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
            'group' => env('DB_GROUP', 'group'),
            'username' => env('DB_USERNAME', 'username'),
            'password' => env('DB_PASSWORD', 'password'),
        ]);

        // Crea il file di flag per simulare la condizione di "pacchetto già installato"
        $alreadyInstalledFlagPath = base_path('mifra_crud_installed.json');
        File::put($alreadyInstalledFlagPath, 'installed'); // Il contenuto del file non è rilevante, basta che il file esista
    }

    protected function getPackageProviders($app)
    {
        return [MifraCrudServiceProvider::class];
    }

    /** @test */
    public function it_creates_a_new_crud()
    {
        $elements =[
            'id' => 1000000,
            'title' => 'Test CRUD Title',
            'desc' => 'Test CRUD Descrizione',
            'route_name' => 'mifracruds.test',
            'group' => 'managements',
        ];

        $output = Artisan::call('mifra:createcrud', [
            'elements' => json_encode($elements), // Passa gli elementi come stringa JSON
            '--delete' => false, // Se vuoi utilizzare l'opzione delete, cambia in true
        ]);

        // Stampo l'output del comando
        //$logs = Artisan::output();
        //echo $logs;

        // Verifica che il comando sia eseguito con successo (ritorna 0)
        //$this->assertEquals(0, $output);

        // Verifica che i dati siano stati inseriti/aggiornati nel database MongoDB
        $collection = DB::connection('mongodb')->collection(env('DB_COLLECTION', 'collection'));
        $insertedItem = $collection->where(['id' => 1000000])->first();

        $this->assertNotNull($insertedItem);
        $this->assertEquals('Test CRUD Title', $insertedItem['title']);

        // Oppure, se stai creando file, verifica che il file esista
        $path = base_path('routes/mifracruds/test.php');
        $this->assertTrue(File::exists($path));

        $path = base_path('app/Http/Controllers/MifraCruds/MifracrudsTestController.php');
        $this->assertTrue(File::exists($path));

        $path = base_path('app/Models/MifraCruds/MifracrudsTestModel.php');
        $this->assertTrue(File::exists($path));

        $path = base_path('resources/views/mifracruds/test/index.blade.php');
        $this->assertTrue(File::exists($path));

        $path = base_path('routes/mifracruds/test.php');
        $this->assertTrue(File::exists($path));
    }

    protected function tearDown(): void
    {
        // Rimuovere il file di flag
        $alreadyInstalledFlagPath = base_path('mifra_crud_installed.json');
        File::delete($alreadyInstalledFlagPath);
        
        // Assicurati di chiamare il tearDown del genitore
        parent::tearDown();

        // Rimuovere i file di controller generati
        $directoryPath = base_path('app/Http/Controllers/MifraCruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere i file di model generati
        $directoryPath = base_path('app/Models/MifraCruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere i file view generati
        $directoryPath = base_path('resources/views/mifracruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere i file di rotta generati
        $directoryPath = base_path('routes/mifracruds');
        File::deleteDirectory($directoryPath);

        DB::connection('mongodb')->collection(env('DB_COLLECTION', 'collection'))->where('id', 1000000)->delete();
    }

}
