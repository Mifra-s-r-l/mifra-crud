<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mifra\Crud\Helpers\CrudHelpers;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
    protected $permissions;

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

        // Lista dei permessi di default
        $this->permissions = config('mifracrud.permissions');
    }

    // Esegue il comando Artisan
    public function handle()
    {
        try {

            $jsonElements = $this->argument('elements');
            $this->elements = json_decode($jsonElements, true); // Decodifica la stringa JSON come array associativo

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Errore nella decodifica della stringa JSON.');
                return;
            }

            $alreadyInstalledFlagPath = base_path('mifra_crud_installed.json');

            if (File::exists($alreadyInstalledFlagPath)) {

                if ($this->option('delete')) {
                    $id = $this->elements['_id'] ?? null;
                    if (!$id) {
                        $this->error('ID non specificato per l\'eliminazione.');
                        return;
                    }
                    $this->deleteItem();
                } else {
                    $this->insertItem();
                    if (isset($this->elements['route_name']) && $this->elements['route_name'] != 'submenu') {
                        $this->createRoute();
                    }
                }

                //$nameCapitalize = ucwords($this->argument('name'));

            } else {
                $this->error('Devi prima installare il pacchetto con il comando da terminale "php artisan mifra:installcrud".');
                return;
            }

        } catch (\Exception $e) {
            $this->info("Errore handle: " . $e->getMessage());
            return;
        }
    }

    protected function deleteItem()
    {
        $className = CrudHelpers::conversionRouteName($this->elements['route_name'], 'className');
        $path = CrudHelpers::conversionRouteName($this->elements['route_name'], 'path');

        // Percorso del file che vuoi cancellare
        $controllerFile = base_path('app/Http/Controllers/MifraCrudsCreated/' . $className . 'Controller.php');
        // Controlla se il file controller esiste e cancellalo
        if (File::exists($controllerFile)) {
            File::delete($controllerFile);
        }

        // Percorso del file che vuoi cancellare
        $modelFile = base_path('app/Models/MifraCrudsCreated/' . $className . 'Model.php');
        // Controlla se il file model esiste e cancellalo
        if (File::exists($modelFile)) {
            File::delete($modelFile);
        }

        // Rimuovere i file delle view
        $viewFile = base_path('resources/views/cruds/' . $path);
        if (File::exists($viewFile)) {
            File::deleteDirectory($viewFile);
        }

        // Percorso del file che vuoi cancellare
        $routeFile = base_path('routes/cruds/' . $path . '.php');
        // Controlla se il file controller esiste e cancellalo
        if (File::exists($routeFile)) {
            File::delete($routeFile);
        }
        // Split del path per ottenere le singole cartelle
        $parts = explode('/', $path);
        $directoryPath = base_path('routes/cruds');

        while (!empty($parts)) {
            $directoryPath .= '/' . array_pop($parts);

            // Verifica se la cartella è vuota prima di eliminarla
            if (File::isDirectory($directoryPath) && File::allFiles($directoryPath) == []) {
                File::deleteDirectory($directoryPath);
            } else {
                // Se la cartella non è vuota, interrompi il ciclo
                break;
            }
        }

        // Percorso al file web.php
        $fileRouteWeb = base_path('routes/cruds/created.php');
        // Leggi il contenuto del file
        $contentRouteWeb = File::get($fileRouteWeb);
        // Rimuovi la riga
        $updatedContentRouteWeb = str_replace("\nrequire __DIR__ . '/" . $path . ".php';", '', $contentRouteWeb);
        // Salva il file aggiornato
        File::put($fileRouteWeb, $updatedContentRouteWeb);

        if (isset($this->elements['createGroup'])) {
            $collection = DB::connection('mongodb')->collection($this->databaseConfig['group']);
        } else {
            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        }
        $deletedCount = $collection->where('_id', new \MongoDB\BSON\ObjectId($this->elements['_id']))->delete();

        $permissionName = CrudHelpers::conversionRouteName($this->elements['route_name'], 'permission');
        foreach ($this->permissions as $permission) {
            // Costruisce il nome del permesso
            $fullPermissionName = $permission . '_' . $permissionName;

            // Trova il permesso per nome
            $permissionToDelete = Permission::where('name', $fullPermissionName)->first();

            // Se il permesso esiste, lo elimina
            if ($permissionToDelete) {
                $permissionToDelete->delete();
            }
        }

        if ($deletedCount > 0) {
            $this->info("Elemento con ID {$this->elements['id']} eliminato con successo.");
        } else {
            $this->error("Nessun elemento trovato con ID {$this->elements['id']} da eliminare.");
        }
    }

    public function insertItem()
    {
        if (isset($this->elements['route_name']) && $this->elements['route_name'] != 'submenu') {
            $this->elements['permissions'] = $this->permissions;
        }

        if (isset($this->elements['createGroup'])) {
            $collection = DB::connection('mongodb')->collection($this->databaseConfig['group']);
        } else {
            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        }

        if (isset($this->elements['_id'])) {
            $collection->where('_id', new \MongoDB\BSON\ObjectId($this->elements['_id']))->update($this->elements);
            $this->info("Aggiornata la voce di menu: {$this->elements['title']}");
        } else {
            $collection->insert($this->elements);
            $this->info("Inserita nuova voce di menu: {$this->elements['title']}");
        }
    }

    protected function createRoute()
    {
        // Creo il ruolo super-admin se non esiste
        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
        ]);
        // Creo il permesso per il nuovo CRUD
        $permissionName = CrudHelpers::conversionRouteName($this->elements['route_name'], 'permission');
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission . '_' . $permissionName]);
            $superAdmin->givePermissionTo($permission . '_' . $permissionName);
        }

        $routePath = CrudHelpers::conversionRouteName($this->elements['route_name'], 'path');
        $className = CrudHelpers::conversionRouteName($this->elements['route_name'], 'className');
        $methodName = $this->elements['method'] ?? 'index';
        $routeName = $this->elements['route_name'];
        $cleanedRoutePath = str_replace("mifracruds/", "", $routePath);

        // Creo il controller
        CrudHelpers::createControllerFile($this, $routeName, 'app/Http/Controllers/MifraCrudsCreated');

        // Creo il model
        CrudHelpers::createModelFile($this, $routeName, 'app/Models/MifraCrudsCreated');

        // Creo il view
        $directoryPathViewCrud = base_path('resources/views/cruds/' . $routePath);
        CrudHelpers::createViewFile($this, $routeName, $directoryPathViewCrud);

        // Assicurati che il file esista o crealo
        $routesFilePath = base_path('routes/cruds/' . $routePath . '.php');
        $directoryPath = dirname($routesFilePath); // Ottiene il percorso della directory

        // Crea la directory se non esiste
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true); // Il terzo parametro "true" consente la creazione di directory nidificate
        }

        // Aggiungi la nuova definizione di rotta al file
        $routeDefinition = "<?php\n\nuse Illuminate\Support\Facades\Route;\nuse App\Http\Controllers\MifraCrudsCreated\\" . $className . "Controller;\n\nRoute::get('" . $routePath . "', [" . $className . "Controller::class, '" . $methodName . "'])->name('" . $routeName . "');\n";
        File::put($routesFilePath, $routeDefinition);

        $routeFilePathCruds = base_path('routes/cruds/created.php');
        File::append($routeFilePathCruds, "\nrequire __DIR__ . '/" . $routePath . ".php';");
    }

}