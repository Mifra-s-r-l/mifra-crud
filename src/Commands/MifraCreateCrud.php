<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mifra\Crud\Helpers\CrudHelpers;

class MifraCreateCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:createcrud
                        {elements : Stringa JSON degli elementi per il CRUD}
                        {--delete : Elimina l\'elemento specificato tramite ID}';

    // Descrizione del comando Artisan
    protected $description = 'Creazione di un nuovo CRUD';

    protected $databaseConfig;
    protected $elements;

    // Crea una nuova istanza del comando
    public function __construct()
    {
        parent::__construct();

        $database = config('mifracrud.database');

        // Configura la connessione MongoDB
        Config::set('database.connections.mongodb', [
            'driver' => 'mongodb',
            'host' => $database['host'],
            'port' => $database['port'],
            'database' => $database['database'],
            'username' => $database['username'],
            'password' => $database['password'],
        ]);

        // Carica la configurazione del database da un file di configurazione
        $this->databaseConfig = $database;
    }

    // Esegue il comando Artisan
    public function handle()
    {
        try {

            $jsonElements = $this->argument('elements');
            $this->elements = json_decode($jsonElements, true); // Decodifica la stringa JSON come array associativo

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Errore nella decodifica della stringa JSON.');
                return 1;
            }

            $alreadyInstalledFlagPath = base_path('.mifra_crud_installed');

            if (File::exists($alreadyInstalledFlagPath)) {

                $id = $this->elements['id'] ?? null;

                if (!$id) {
                    $this->error('ID non specificato per l\'eliminazione.');
                    return 1;
                }

                if ($this->option('delete')) {
                    $this->deleteMenuItem();
                } else {
                    $this->insertMenuItem();
                    $this->createRoute();
                }

                //$nameCapitalize = ucwords($this->argument('name'));

            } else {
                $this->error('Devi prima installare il pacchetto con il comando da terminale "php artisan mifra:installcrud".');
                return 1;
            }

        } catch (\Exception $e) {
            $this->info("Errore handle: " . $e->getMessage());
            return 1;
        }
    }

    protected function deleteMenuItem()
    {
        $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        $deletedCount = $collection->where('id', intval($this->elements['id']))->delete();

        if ($deletedCount > 0) {
            $this->info("Elemento con ID {$this->elements['id']} eliminato con successo.");
        } else {
            $this->error("Nessun elemento trovato con ID {$this->elements['id']} da eliminare.");
        }
    }

    public function insertMenuItem()
    {
        $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        $exists = $collection->where('id', intval($this->elements['id']))->first(); // Verifica l'esistenza dell'elemento

        if (!$exists) {
            $collection->insert($this->elements);
            $this->info("Inserita nuova voce di menu: {$this->elements['title']}");
        } else {
            $collection->where('id', intval($this->elements['id']))->update($this->elements);
            $this->info("Aggiornata la voce di menu: {$this->elements['title']}");
        }
    }

    protected function createRoute()
    {
        $routePath = CrudHelpers::conversionRouteName($this->elements['route_name'], 'path');
        $className = CrudHelpers::conversionRouteName($this->elements['route_name'], 'className');
        $methodName = $this->elements['method'] ?? 'index';
        $routeName = $this->elements['route_name'];

        // Creo il controller
        CrudHelpers::createControllerFile($this, $routeName, 'app/Http/Controllers/MifraCruds');

        // Creo il model
        CrudHelpers::createModelFile($this, $routeName, 'app/Models/MifraCruds');

        // Creo il view
        CrudHelpers::createViewFile($this, $routeName);

        // Assicurati che il file esista o crealo
        $routesFilePath = base_path('routes/' . $routePath . '.php');
        $directoryPath = dirname($routesFilePath); // Ottiene il percorso della directory

        // Crea la directory se non esiste
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true); // Il terzo parametro "true" consente la creazione di directory nidificate
        }

        // Aggiungi la nuova definizione di rotta al file
        $routeDefinition = "<?php\n\nuse Illuminate\Support\Facades\Route;\nuse App\Http\Controllers\MifraCruds\\".$className."Controller;\n\nRoute::get('".$routePath."', [".$className."Controller::class, '".$methodName."'])->name('".$routeName."');\n";
        File::put($routesFilePath, $routeDefinition);

        $routeFilePathCruds = base_path('routes/mifracruds/cruds.php');
        File::append($routeFilePathCruds, "\n\nrequire __DIR__ . '/prova.php';");
    }

}
