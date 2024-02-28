<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MifraInstallCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:installcrud {--reset : Reinstalla il CRUD sovrascrivendo i file esistenti}';

    // Descrizione del comando Artisan
    protected $description = 'Installazione del sistema CRUD';

    protected $databaseConfig;
    protected $groupsMenus;
    protected $jsonConfig;
    protected $directoryPathController;
    protected $directoryPathModel;
    protected $directoryPathRoute;

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

        // Imposta 'mongodb' come connessione di database predefinita
        //Config::set('database.default', 'mongodb');

        // Carica la configurazione del database da un file di configurazione
        $this->databaseConfig = $database;

        $this->groupsMenus = config('mifracrud.groups_menus');

        // Carica il percorso del file JSON da un file di configurazione
        $this->jsonConfig = config('mifracrud.menus');

        // Assicurati che questa directory esista o sia creata
        $this->directoryPathController = base_path('app/Http/Controllers/MifraCruds');
        if (!File::exists($this->directoryPathController)) {
            File::makeDirectory($this->directoryPathController, 0755, true);
        }

        // Assicurati che questa directory esista o sia creata
        $this->directoryPathModel = base_path('app/Models/MifraCruds');
        if (!File::exists($this->directoryPathModel)) {
            File::makeDirectory($this->directoryPathModel, 0755, true);
        }

        // Assicurati che questa directory esista o sia creata
        $this->directoryPathRoute = base_path('routes/mifracruds');
        if (!File::exists($this->directoryPathRoute)) {
            File::makeDirectory($this->directoryPathRoute, 0755, true);
        }

        $filePathWeb = base_path('routes/web.php');
        if (!File::exists($filePathWeb)) {
            // Contenuto che vuoi scrivere nel file
            $fileContent = "<?php\nrequire __DIR__ . '/mifracruds/cruds.php';";

            // Crea il file e scrivi il contenuto
            File::put($filePathWeb, $fileContent);
        }
    }

    // Esegue il comando Artisan
    public function handle()
    {
        $alreadyInstalledFlagPath = base_path('.mifra_crud_installed');

        if (File::exists($alreadyInstalledFlagPath) && !$this->option('reset')) {
            $this->info("Il CRUD Mifra è già stato installato. Usa l'opzione --reset per reinstallarlo.");
            return;
        }

        // Se --reset è specificato o se il CRUD non è stato ancora installato, procedi con l'installazione
        if ($this->option('reset')) {
            $this->info("Reinstallazione del CRUD Mifra...");

            $this->installCrud();
        } else {
            $this->info("Installazione del CRUD Mifra...");
            // Crea un file di flag per indicare che l'installazione è stata completata
            File::put($alreadyInstalledFlagPath, 'Installed');

            $this->installCrud();
        }
    }

    public function installCrud()
    {
        try {
            $this->info("Connessione al database...");
            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');

            //DB::connection('mongodb')->collection($this->databaseConfig['collection'])->delete();

            // Creo il gruppo dei CRUD di default
            $group = DB::connection('mongodb')->collection($this->databaseConfig['group']);
            $exists = $group->where('id', 1)->first(); // Verifica l'esistenza dell'elemento
            if (!$exists) {
                // Se non esiste, inseriscilo nel database
                $group->insert($this->groupsMenus);
            } else {
                // Se esiste, aggiornalo con i nuovi valori
                $group->where('id', 1)->update(['$set' => $this->groupsMenus]);
            }

            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection'])->get();

            $this->info("Creazione voci di menù principali...");
            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');

            $this->insertMenuItems();

            // Aggiungi il require a routes/web.php
            $this->addRequireToWebRoutes();

        } catch (\Exception $e) {
            $this->info("Errore installCrud: " . $e->getMessage());
            return 1;
        }
    }

    public function converionRouteName($route_name, $return)
    {
        $result = '';

        switch ($return) {

            case "path":
                $result = str_replace(".", "/", $route_name);
                break;

            case "className":
                $items = explode(".", $route_name);
                foreach ($items as $item) {
                    $result .= ucwords($item);
                }
                break;

        }

        return $result;
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

            // Creo il model
            $this->createModelFile($menuItem);

            // Creo la view
            $this->createViewFile($menuItem);

            $className = $this->converionRouteName($menuItem['route_name'], 'className');

            // Crea contenuto per il file delle rotte per la nuova voce di menu
            $routeContent .= $this->createContenRouteFile($menuItem);
            $routeContentHead .= "use App\Http\Controllers\MifraCruds\\{$className}Controller;\n";

            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');
        }

        // Creo il file delle rotte
        $routeFilePath = $this->directoryPathRoute . '/cruds.php';
        File::put($routeFilePath, "<?php\n\nuse Illuminate\Support\Facades\Route;\n\nuse App\Http\Controllers\MifraCruds\MifracrudsController;\n");
        // Scrivi l'header e il contenuto delle rotte nel file
        File::append($routeFilePath, $routeContentHead . $routeContent);

        // Aggiungo le rotte per la creazione e eliminazione dei CRUD di default
        $this->createCommandsDefault($routeFilePath);
    }

    protected function createCommandsDefault($routeFilePath)
    {
        $menuItem['route_name'] = 'mifracruds';
        $this->createControllerFile($menuItem);

        // Segnaposto da cercare
        $placeholderHead = 'use Illuminate\Routing\Controller;';

        // Segnaposto da cercare
        $placeholder = '// PLACEHOLDER_FOR_NEW_METHODS';

        // Definizione del pattern per identificare esattamente il metodo index()
        $pattern = '/\s*public\s+function\s+index\(\)\s*\{[^\}]*\}\s*/s';

        // Percorsi e lettura dei file stub per le funzioni create e delete
        $stubPathDefault = __DIR__ . '/../resources/stubs/functions/cruds/default.stub';

        // Verifica dell'esistenza e lettura dei contenuti degli stub
        if (!file_exists($stubPathDefault)) {
            $this->error("Il file stub {$stubPathDefault} non esiste.");
            return 1;
        }

        // Lettura del contenuto dei file stub
        $defaultContent = File::get($stubPathDefault);

        // Costruisci i percorsi del file .stub
        $stubPathController = base_path('app/Http/Controllers/MifraCruds/MifracrudsController.php');

        // Verifica dell'esistenza dello stub
        if (!file_exists($stubPathController)) {
            $this->error("Il file stub {$stubPathController} non esiste.");
            return 1;
        }

        // Lettura del contenuto dei file controller
        $controllerTemplate = File::get($stubPathController);

        // Rimozione del metodo index()
        $controllerContent = preg_replace($pattern, "\n", $controllerTemplate);

        // Prepara la linea da aggiungere
        $useArtisanLine = "use Illuminate\Support\Facades\Artisan;";
        
        // Trova la posizione del segnaposto per l'header e aggiungi la nuova linea dopo
        $newControllerContent = preg_replace("/(" . preg_quote($placeholderHead, '/') . ")/", "$1\n" . $useArtisanLine, $controllerContent);

        // Sostituisci il segnaposto con il contenuto delle nuove funzioni
        $newControllerContent = str_replace($placeholder, $defaultContent . "\n    " . $placeholder, $newControllerContent);

        File::put($stubPathController, $newControllerContent);

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/routes/cruds/default.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $commandsTemplate = File::get($stubPath);

        File::append($routeFilePath, "\n" . $commandsTemplate);

    }

    protected function createControllerFile($menuItem)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/CrudController.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = $this->converionRouteName($menuItem['route_name'], 'className');

        $controllerTemplate = File::get($stubPath);
        $controllerContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $menuItem['route_name']], $controllerTemplate);

        $controllerFilePath = $this->directoryPathController . "/{$className}Controller.php";
        File::put($controllerFilePath, $controllerContent);

        $this->info("Creato/Aggiornato il controller: App\Http\Controllers\MifraCruds\\{$className}Controller, qui puoi inserire il tuo codice per gestire la logica della vista");
    }

    protected function createModelFile($menuItem)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/CrudModel.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = $this->converionRouteName($menuItem['route_name'], 'className');

        $modelTemplate = File::get($stubPath);
        $modelContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $menuItem['route_name']], $modelTemplate);

        $modelFilePath = $this->directoryPathModel . "/{$className}Model.php";
        File::put($modelFilePath, $modelContent);

        $this->info("Creato/Aggiornato il model: App\Models\MifraCruds\\{$className}Model, qui puoi inserire il tuo codice per gestire il database della vista");
    }

    protected function createViewFile($menuItem)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/index.blade.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        // Assicurati che questa directory esista o sia creata
        $route_names = explode(".", $menuItem['route_name']);
        $dirPathResources = "";

        foreach ($route_names as $route_name) {
            $dirPathResources .= $route_name . "/";
        }
        $dirPathResources = rtrim($dirPathResources, '/');

        $directoryPathViewCrud = base_path('resources/views/' . $dirPathResources);
        if (!File::exists($directoryPathViewCrud)) {
            File::makeDirectory($directoryPathViewCrud, 0755, true);
        }

        $viewTemplate = File::get($stubPath);
        $viewContent = str_replace(['%%route_name%%', '%%path%%'], [$menuItem['route_name'], $dirPathResources], $viewTemplate);

        $viewFilePath = $directoryPathViewCrud . "/index.blade.php";
        File::put($viewFilePath, $viewContent);

        $this->info("Creato/Aggiornato il file view: resources/views/{$dirPathResources}/index.blade.php, adesso basta creare il file index.blade.php in questo percorso pages/" . $dirPathResources . " per la grafica della vista");
    }

    protected function createContenRouteFile($menuItem)
    {
        $stubPath = __DIR__ . '/../resources/stubs/route.stub';
        if (!File::exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = $this->converionRouteName($menuItem['route_name'], 'className');
        $path = $this->converionRouteName($menuItem['route_name'], 'path');

        $routeTemplate = File::get($stubPath);
        $routeContent = str_replace(['{{path}}', '{{crud_name}}', '{{route_name}}'], [$path, $className, $menuItem['route_name']], $routeTemplate);

        $this->info("Inserita/Aggiornata rotta nel file: routes/mifracruds/cruds.php, adesso per utilizzarla basta fare cosi, es: route('" . $menuItem['route_name'] . "') o inserirla nella gestione del menu del template");

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
