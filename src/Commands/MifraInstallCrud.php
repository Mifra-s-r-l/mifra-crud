<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Mifra\Crud\Helpers\CrudHelpers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class MifraInstallCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:installcrud 
                        {--uninstall : Disinstallazione di CRUD}
                        {--reset : Reinstalla il CRUD sovrascrivendo i file di default}
                        {--hardreset : Reinstalla il CRUD sovrascrivendo tutti i file esistenti}';

    // Descrizione del comando Artisan
    protected $description = 'Installazione del sistema CRUD';

    protected $groupsMenus;
    protected $jsonConfig;
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
    }

    // Esegue il comando Artisan
    public function handle()
    {
        $alreadyInstalledFlagPath = base_path('.mifra_crud_installed');

        if (File::exists($alreadyInstalledFlagPath) && (!$this->option('hardreset') && !$this->option('reset') && !$this->option('uninstall'))) {
            $this->info("Il CRUD Mifra è già stato installato. Usa il comando 'php artisan mifra:installcrud --help' per visualizzare i comandi a disposizione.");
            return;
        }

        // Se --reset è specificato o se il CRUD non è stato ancora installato, procedi con l'installazione
        if ($this->option('reset')) {
            $this->info("Reinstallazione del CRUD Mifra di default...");
            $this->installCrud();
        } else if ($this->option('hardreset')) {
            $this->info("Reinstallazione del CRUD Mifra totale...");
            $this->deleteData();
            $this->installCrud();
        } else if ($this->option('uninstall')) {
            $this->info("Disinstallazione del CRUD Mifra...");
            $this->deleteData();
        } else {
            $this->info("Installazione del CRUD Mifra...");
            // Crea un file di flag per indicare che l'installazione è stata completata
            File::put($alreadyInstalledFlagPath, 'Installed');
            $this->installCrud();
        }
    }

    public function deleteData()
    {
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

        // Percorso al file web.php
        $fileRouteWeb = base_path('routes/web.php');

        // Leggi il contenuto del file
        $contentRouteWeb = File::get($fileRouteWeb);

        // Rimuovi la riga
        $updatedContentRouteWeb = str_replace("require __DIR__ . '/mifracruds/cruds.php';\n", '', $contentRouteWeb);

        // Salva il file aggiornato
        File::put($fileRouteWeb, $updatedContentRouteWeb);

        // Percorso del file che vuoi cancellare
        $alreadyInstalledFlagPath = base_path('.mifra_crud_installed');

        // Controlla se il file esiste e cancellalo
        if (File::exists($alreadyInstalledFlagPath)) {
            File::delete($alreadyInstalledFlagPath);
        }

        DB::connection('mongodb')->getMongoDB()->drop();
    }

    public function installCrud()
    {
        // Assicurati che questa directory esista o sia creata
        $directoryPath = base_path('routes/mifracruds');
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
        $filePathWeb = base_path('routes/web.php');
        if (!File::exists($filePathWeb)) {
            // Contenuto che vuoi scrivere nel file
            $fileContent = "<?php\nrequire __DIR__ . '/mifracruds/cruds.php';";

            // Crea il file e scrivi il contenuto
            File::put($filePathWeb, $fileContent);
        }

        try {
            $this->info("Connessione al database...");
            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');

            // Creo il gruppo dei CRUD di default
            DB::connection('mongodb')->collection($this->databaseConfig['group'])->delete();
            $group = DB::connection('mongodb')->collection($this->databaseConfig['group']);
            // Se esiste, aggiornalo con i nuovi valori
            foreach ($this->groupsMenus as $groupsMenu) {
                # code...
                $group->where('id', $groupsMenu['id'])->insert($groupsMenu);
            }

            DB::connection('mongodb')->collection($this->databaseConfig['collection'])->delete();
            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection'])->get();

            $this->info("Creazione voci di menù principali...");
            // Messaggio di separazione per migliorare la leggibilità dell'output
            $this->info('');

            $this->insertMenuItems();

            // Aggiungi il require a routes/web.php
            $this->addRequireToWebRoutes();

        } catch (\Exception $e) {
            $this->info("Errore installCrud: " . $e->getMessage());
            return;
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
            CrudHelpers::createControllerFile($this, $menuItem['route_name'], 'app/Http/Controllers/MifraCruds');

            // Creo il model
            CrudHelpers::createModelFile($this, $menuItem['route_name'], 'app/Models/MifraCruds');

            // Creo la view
            CrudHelpers::createViewFile($this, $menuItem['route_name']);

            $className = CrudHelpers::conversionRouteName($menuItem['route_name'], 'className');

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
        if (!File::exists($stubPathDefault)) {
            $this->error("Il file stub {$stubPathDefault} non esiste.");
            return;
        }

        // Lettura del contenuto dei file stub
        $defaultContent = File::get($stubPathDefault);

        // Costruisci i percorsi del file .stub
        $stubPathController = base_path('app/Http/Controllers/MifraCruds/MifracrudsController.php');

        // Verifica dell'esistenza dello stub
        if (!File::exists($stubPathController)) {
            $this->error("Il file stub {$stubPathController} non esiste.");
            return;
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

        if (!File::exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return;
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

        $className = CrudHelpers::conversionRouteName($menuItem['route_name'], 'className');

        $controllerTemplate = File::get($stubPath);
        $controllerContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $menuItem['route_name']], $controllerTemplate);

        $controllerFilePath = $this->directoryPathController . "/{$className}Controller.php";
        File::put($controllerFilePath, $controllerContent);

        $this->info("Creato/Aggiornato il controller: App\Http\Controllers\MifraCruds\\{$className}Controller, qui puoi inserire il tuo codice per gestire la logica della vista");
    }

    protected function createContenRouteFile($menuItem)
    {
        $stubPath = __DIR__ . '/../resources/stubs/route.stub';
        if (!File::exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return;
        }

        $className = CrudHelpers::conversionRouteName($menuItem['route_name'], 'className');
        $path = CrudHelpers::conversionRouteName($menuItem['route_name'], 'path');

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
