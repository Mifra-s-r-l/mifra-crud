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
    }

    // Esegue il comando Artisan
    public function handle()
    {
        try {
            $this->info("Connessione al database...");

            DB::connection('mongodb')->collection($this->databaseConfig['collection'])->delete();

            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection'])->get();

            $this->info("Creazione voci di menù principali...");
            $this->insertMenuItems();

            $this->info('Creazione delle rotte...');

            // Aggiungi il require a routes/web.php
            $this->addRequireToWebRoutes();

        } catch (\Exception $e) {
            $this->info("Errore durante la connessione al database MongoDB: " . $e->getMessage());
            return 1;
        }
    }

    public function insertMenuItems()
    {
        // Assicurati che questa directory esista o sia creata
        $directoryPath = base_path('routes/mifracruds');
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }

        $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        $menuItems = $this->jsonConfig; // Voci di menu del file di config
        $routeFilePath = $directoryPath . '/cruds.php';
        File::put($routeFilePath, "<?php\n");

        foreach ($menuItems as $menuItem) {
            $exists = $collection->where('id', $menuItem['id'])->exists(); // Verifica l'esistenza dell'elemento

            if (!$exists) {
                // Se non esiste, inseriscilo nel database
                $collection->insert($menuItem);
                $this->info("Inserita nuova voce di menu: {$menuItem['title']}");

                // Crea il file di rotte per la nuova voce di menu
                $this->createRouteFile($menuItem, $directoryPath, $routeFilePath);
            } else {
                // Opzionale: messaggio se l'elemento esiste già
                $this->info("La voce di menu: {$menuItem['title']} esiste già.");
            }
        }
    }

    protected function createRouteFile($menuItem, $directoryPath, $routeFilePath)
    {
        $stubPath = __DIR__ . '/../resources/stubs/route.stub';
        if (!File::exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $routeTemplate = File::get($stubPath);
        $routeContent = str_replace(['{{path}}', '{{controller_name}}', '{{route_name}}'], [$menuItem['path'], $menuItem['controller_name'], $menuItem['route_name']], $routeTemplate);

        File::append($routeFilePath, $routeContent);
        $this->info("Creato il file di rotte per: {$menuItem['title']}");
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
                $this->comment("Il require di mifracruds/cruds.php è già presente in routes/web.php");
            }
        } else {
            $this->error("Il file routes/web.php non esiste quindi per far funzionare le rotte bisogna attivarle manualmente.");
        }
    }

}
