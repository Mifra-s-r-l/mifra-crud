<?php

namespace Mifra\Crud\Commands;

use Illuminate\Console\Command;

class MifraCreateCrud extends Command
{
    // Il nome e la firma del comando Artisan
    protected $signature = 'mifra:createcrud {name : Il nome del CRUD controller}';

    // Descrizione del comando Artisan
    protected $description = 'Creazione di un nuovo crud';

    // Crea una nuova istanza del comando
    public function __construct()
    {
        parent::__construct();
    }

    // Esegue il comando Artisan
    public function handle()
    {
        $this->info('Creazione di un nuovo CRUD');

        // Chiedi il nome del Controller all'utente
        $name = $this->argument('name');

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__.'/../resources/stubs/CrudController.stub';

        if (!file_exists($stubPath)) {
            $this->error("Il file stub {$stubPath} non esiste.");
            return;
        }        

        // Leggi il contenuto del file .stub
        $stub = file_get_contents($stubPath);

        // Sostituisci il segnaposto con il nome reale del Controller
        $stub = str_replace('DummyClass', $name, $stub);

        // Definisci il percorso dove il nuovo file Controller sarà salvato
        $controllerPath = app_path("Http/Controllers/{$name}.php");

        // Salva il nuovo file Controller
        if (false === file_put_contents($controllerPath, $stub)) {
            $this->error("Impossibile scrivere il controller in {$controllerPath}.");
            return;
        }        

        $this->info("Controller {$name} creato con successo.");
    }
}
