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
        $logs = Artisan::output();
        echo $logs;

        // Verifica che il comando sia eseguito con successo (ritorna 0)
        $this->assertEquals(0, $output);

        // Verifica che la configurazione del database sia stata impostata correttamente
        $this->assertEquals('mongodb', config('database.default'));
        $this->assertNotEmpty(config('database.connections.mongodb'));

        // Opzionale: verifica il messaggio di output se il tuo comando ne produce uno specifico
        // $this->assertStringContainsString('Creazione delle rotte completata', Artisan::output());
    }

    protected function tearDown(): void
    {
        // Assicurati di chiamare il tearDown del genitore
        parent::tearDown();

        // Utilizza la facciata DB di Laravel per connetterti al database MongoDB
        //$collection = DB::connection('mongodb')->collection(env('DB_COLLECTION', 'collection'));

        // Puoi cancellare documenti specifici utilizzando la funzione delete() con criteri specifici
        // $collection->where('campo', 'valore')->delete();

        // Oppure, per cancellare tutti i documenti in una collezione (Attenzione: questo rimuoverà TUTTI i documenti!)
        //$collection->delete();

        // Alternativamente, se vuoi rimuovere interamente la collezione (Attenzione: questo rimuoverà la collezione stessa!)
        // $collection->drop();

        // Rimuovere tutti i documenti dalla collezione specificata
        DB::connection('mongodb')->collection(env('DB_COLLECTION'))->delete();

        // Rimuovere i file di controller generati

        // Rimuovere i file di rotta generati
        //$directoryPath = base_path('routes/mifracruds');
        //File::deleteDirectory($directoryPath);

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
