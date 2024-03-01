<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MifraCreateGroupCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:creategroupcrud
                        {elements : Stringa JSON degli elementi per il CRUD}
                        {--delete : Elimina l\'elemento specificato tramite ID}';

    // Descrizione del comando Artisan
    protected $description = 'Creazione di un nuovo grouppo di CRUD';

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
                return;
            }

            $alreadyInstalledFlagPath = base_path('.mifra_crud_installed');

            if (File::exists($alreadyInstalledFlagPath)) {

                $id = $this->elements['id'] ?? null;

                if (!$id) {
                    $this->error('ID non specificato per l\'eliminazione.');
                    return;
                }

                if ($this->option('delete')) {
                    $this->deleteMenuItem();
                } else {
                    $this->insertMenuItem();
                }

            } else {
                $this->error('Devi prima installare il pacchetto con il comando da terminale "php artisan mifra:installcrud".');
                return;
            }

        } catch (\Exception $e) {
            $this->info("Errore handle: " . $e->getMessage());
            return;
        }
    }

    protected function deleteMenuItem()
    {
        $group = DB::connection('mongodb')->group($this->databaseConfig['group']);
        $deletedCount = $group->where('id', intval($this->elements['id']))->delete();

        if ($deletedCount > 0) {
            $this->info("Elemento con ID {$this->elements['id']} eliminato con successo.");
        } else {
            $this->error("Nessun elemento trovato con ID {$this->elements['id']} da eliminare.");
        }
    }

    public function insertMenuItem()
    {
        $group = DB::connection('mongodb')->group($this->databaseConfig['group']);
        $exists = $group->where('id', intval($this->elements['id']))->first(); // Verifica l'esistenza dell'elemento

        if (!$exists) {
            $group->insert($this->elements);
            $this->info("Inserita nuova voce di menu: {$this->elements['title']}");
        } else {
            $group->where('id', intval($this->elements['id']))->update($this->elements);
            $this->info("Aggiornata la voce di menu: {$this->elements['title']}");
        }
    }
}
