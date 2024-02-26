<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MifraInstallCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:installcrud';

    // Descrizione del comando Artisan
    protected $description = 'Creazione delle voci rotte principali e connessione al database';

    protected $databaseConfig;
    protected $menusConfig;
    protected $directoryPathController;
    protected $directoryPathRoute;

    // Crea una nuova istanza del comando
    public function __construct()
    {
        parent::__construct();

        $database = config('mifracrud.database');

        // Configura la connessione MongoDB per i test
        Config::set('database.connections.mongodb', [
            'driver' => 'mongodb',
            'host' => $database['host'],
            'port' => 27017,
            'database' => $database['database'],
            'username' => $database['username'],
            'password' => $database['password'],
        ]);

        // Imposta 'mongodb' come connessione di database predefinita per i test
        Config::set('database.default', 'mongodb');

        // Carica la configurazione del database da un file di configurazione
        $this->databaseConfig = $database;

        // Carica il percorso del file JSON da un file di configurazione
        $this->jsonConfig = config('mifracrud.menus');


        // Assicurati che questa directory esista o sia creata
        $this->directoryPathController = base_path('app/Http/Controllers/MifraCrud');
        if (!File::exists($this->directoryPathController)) {
            File::makeDirectory($this->directoryPathController, 0755, true);
        }

        // Assicurati che questa directory esista o sia creata
        $this->directoryPathRoute = base_path('routes/mifracruds'); 
        if (!File::exists($this->directoryPathRoute)) {
            File::makeDirectory($this->directoryPathRoute, 0755, true);
        }
    }

    // Esegue il comando Artisan
    public function handle()
    {
        try {
            $this->info("Connessione al database...");
            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');

            //DB::connection('mongodb')->collection($this->databaseConfig['collection'])->delete();

            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection'])->get();

            $this->info("Creazione voci di menù principali...");
            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');
            
            $this->insertMenuItems();

            // Aggiungi il require a routes/web.php
            $this->addRequireToWebRoutes();

        } catch (\Exception $e) {
            $this->info("Errore durante la connessione al database MongoDB: " . $e->getMessage());
            return 1;
        }
    }

    public function insertMenuItems()
    {
        $menuItems = $this->jsonConfig; // Voci di menu del file di config

        $routeContentHead = "";
        $routeContent = "";

        foreach ($menuItems as $menuItem) {

            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
            $exists = $collection->where('id', $menuItem['id'])->first(); // Verifica l'esistenza dell'elemento
            
            if (!$exists) {
                // Se non esiste, inseriscilo nel database
                $collection->insert($menuItem);
                $this->info("Inserita nuova voce di menu: {$menuItem['title']}");
            } else {
                // Se esiste, aggiornalo con i nuovi valori
                $collection->where('id', $menuItem['id'])->update($menuItem);
                $this->info("Aggiorno la voce di menu: {$menuItem['title']}");
            }
            
            // Creo il controller
            $this->createControllerFile($menuItem);
            
            // Creo la view
            $this->createViewFile($menuItem);

            // Crea contenuto per il file delle rotte per la nuova voce di menu
            $routeContent .= $this->createContenRouteFile($menuItem);
            $routeContentHead .= "use App\Http\Controllers\MifraCrud\\{$menuItem['controller_name']};\n";

            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');
        }

        // Creo il file delle rotte
        $routeFilePath = $this->directoryPathRoute . '/cruds.php';
        File::put($routeFilePath, "<?php\n\nuse Illuminate\Support\Facades\Route;\n");
        // Scrivi l'header e il contenuto delle rotte nel file
        File::append($routeFilePath, $routeContentHead . $routeContent);
    }

    protected function createControllerFile($menuItem)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__.'/../resources/stubs/CrudController.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        } 

        $controllerTemplate = File::get($stubPath);
        $controllerContent = str_replace(['{{controller_name}}', '{{route_name}}'], [$menuItem['controller_name'], $menuItem['route_name']], $controllerTemplate);

        $controllerFilePath = $this->directoryPathController . "/{$menuItem['controller_name']}.php";
        File::put($controllerFilePath, $controllerContent);

        $this->info("Creato il controller: App\Http\Controllers\MifraCrud\\{$menuItem['controller_name']}");
    }

    protected function createViewFile($menuItem)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__.'/../resources/stubs/index.blade.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        } 

        $viewTemplate = File::get($stubPath);
        $viewContent = str_replace(['%%route_name%%'], [ucwords($menuItem['route_name'])], $viewTemplate);

        // Assicurati che questa directory esista o sia creata
        $route_names = explode(".", $menuItem['route_name']);
        $dirPathResources = "";

        foreach ($route_names as $route_name) {
            $dirPathResources .= $route_name."/";
        }
        $dirPathResources = rtrim($dirPathResources, '/');

        $directoryPathViewCrud = base_path('resources/views/'.$dirPathResources); 
        if (!File::exists($directoryPathViewCrud)) {
            File::makeDirectory($directoryPathViewCrud, 0755, true);
        }

        $viewFilePath = $directoryPathViewCrud  . "/index.blade.php";
        File::put($viewFilePath, $viewContent);

        $this->info("Creato il file view: resources/views/{$dirPathResources}/index.blade.php");
    }

    protected function createContenRouteFile($menuItem)
    {
        $stubPath = __DIR__ . '/../resources/stubs/route.stub';
        if (!File::exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $routeTemplate = File::get($stubPath);
        $routeContent = str_replace(['{{path}}', '{{controller_name}}', '{{route_name}}'], [$menuItem['path'], $menuItem['controller_name'], $menuItem['route_name']], $routeTemplate);
        
        $this->info("Inserita rotta nel file: routes/mifracruds/cruds.php");

        return $routeContent;
    }

    protected function addRequireToWebRoutes()
    {
        $webRoutesPath = base_path('routes/web.php');
        $mifracrudsPath = "__DIR__ . '/mifracruds/cruds.php';";

        if (File::exists($webRoutesPath)) {
            $webRoutesContent = File::get($webRoutesPath);

            // Verifica se il require è già presente per evitare duplicati
            if (strpos($webRoutesContent, $mifracrudsPath) === false) {
                // Aggiungi il require se non è presente
                File::append($webRoutesPath, "\nrequire {$mifracrudsPath}\n");
                $this->info("Aggiunto il require di mifracruds/cruds.php in routes/web.php");
            } else {
                $this->info("Il require di mifracruds/cruds.php è già presente in routes/web.php");
            }
        } else {
            $this->comment("Il file routes/web.php non esiste quindi per far funzionare le rotte bisogna attivarle manualmente.");
        }
    }

}
