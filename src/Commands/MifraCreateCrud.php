<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MifraCreateCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:createcrud {elements : Stringa JSON degli elementi per il CRUD}';

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
        $jsonElements = $this->argument('elements');
        $this->elements = json_decode($jsonElements, true); // Decodifica la stringa JSON come array associativo

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Errore nella decodifica della stringa JSON.');
            return 1;
        }

        $alreadyInstalledFlagPath = base_path('.mifra_crud_installed');

        if (File::exists($alreadyInstalledFlagPath)) {

            //$nameCapitalize = ucwords($this->argument('name'));

            try {
                $this->info("Creazione voce di menù principali...");
                // Messaggio di separazione per migliorare la leggibilità dell'output
                $this->info('');

                $this->insertMenuItem();

            } catch (\Exception $e) {
                $this->info("Errore handle: " . $e->getMessage());
                return 1;
            }

        }
    }

    public function insertMenuItem()
    {
        $collection = DB::connection('mongodb')->collection($this->databaseConfig['collection']);
        
        if (!$this->elements['id']) {
            // Se non esiste, inseriscilo nel database
            $collection->insert($this->elements);
            $this->info("Inserita nuova voce di menu: {$this->elements['title']}");
        } else {
            // Se esiste, aggiornalo con i nuovi valori
            $collection->where('id', $this->elements['id'])->update($this->elements);
            $this->info("Aggiorno la voce di menu: {$this->elements['title']}");
        }
    }

}
