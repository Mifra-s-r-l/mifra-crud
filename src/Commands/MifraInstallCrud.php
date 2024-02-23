<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

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
            'driver'   => 'mongodb',
            'host'     => $database['host'],
            'port'     => 27017,
            'database' => $database['database'],
            'username' => $database['username'],
            'password' => $database['password']
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

            $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection'])->get();
            
        } catch (\Exception $e) {
            $this->info("Errore durante la connessione al database MongoDB: ". $e->getMessage());
            return 1;
        }
        
        $this->info("Creazione voci di menù principali...");
        $this->insertMenuItems();    

        $this->info('Creazione delle rotte...');
    }

    public function insertMenuItems()
    {
        $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        $menuItems = $this->jsonConfig;
        foreach ($menuItems as $menuItem) {
            // Assumi che 'id' o 'key' siano gli identificatori univoci per le voci di menu
            $exists = $collection->where('id', $menuItem['id'])->exists(); // Verifica l'esistenza dell'elemento

            if (!$exists) {
                // Se non esiste, inseriscilo
                $collection->insert($menuItem);
                $this->info("Inserita nuova voce di menu: {$menuItem['title']}");
            } else {
                // Opzionale: messaggio se l'elemento esiste già
                $this->info("La voce di menu: {$menuItem['title']} esiste già.");
            }
        }
    }

}
