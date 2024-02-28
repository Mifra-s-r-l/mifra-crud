<?php

namespace Mifra\Crud\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mifra\Crud\MifraCrudServiceProvider;
use Orchestra\Testbench\TestCase;

class InstallCrudCommandTest extends TestCase
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
    }

    protected function getPackageProviders($app)
    {
        return [MifraCrudServiceProvider::class];
    }

    /** @test */
    public function it_install()
    {
        // Esecuzione del comando
        $output = Artisan::call('mifra:installcrud');

        // Stampo l'output del comando
        //$logs = Artisan::output();
        //echo $logs;

        // Verifica che il comando sia eseguito con successo (ritorna 0)
        //$this->assertEquals(0, $output);

        // Verifica che la configurazione del database sia stata impostata correttamente
        $this->assertEquals('mongodb', config('database.default'));
        $this->assertNotEmpty(config('database.connections.mongodb'));
    }

    protected function tearDown(): void
    {
        // Assicurati di chiamare il tearDown del genitore
        parent::tearDown();

        // Rimuovere i file di controller generati
        $directoryPath = base_path('app/Http/Controllers/MifraCruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere i file di models generati
        $directoryPath = base_path('app/Models/MifraCruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere i file di view generati
        $directoryPath = base_path('resources/views/mifracruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere i file di rotta generati
        $directoryPath = base_path('routes/mifracruds');
        File::deleteDirectory($directoryPath);

        // Rimuovere il require da routes/web.php, se presente
        $webRoutesPath = base_path('routes/web.php');
        if (File::exists($webRoutesPath)) {
            $webRoutesContent = File::get($webRoutesPath);

            // Definisci il percorso che vuoi rimuovere, assicurati che corrisponda esattamente a quello che aggiungi
            $mifracrudsPath = "__DIR__ . '/mifracruds/cruds.php';";
            $requireString = "require {$mifracrudsPath};";

            // Rimuovi la linea
            $newContent = str_replace("<?php {$requireString}\n", '', $webRoutesContent);

            // Sovrascrivi il file con il nuovo contenuto
            File::put($webRoutesPath, $newContent);
        }
    }

}
