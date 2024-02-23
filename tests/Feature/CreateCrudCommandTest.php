<?php

namespace Mifra\Crud\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Mifra\Crud\MifraCrudServiceProvider;

class CreateCrudCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MifraCrudServiceProvider::class];
    }

    /** @test */
    public function it_creates_a_new_controller()
    {
        // Preparazione: Assicurati che il file non esista prima di eseguire il test
        $controllerName = 'TestCrudController';
        $controllerPath = app_path("Http/Controllers/{$controllerName}.php");
        if (File::exists($controllerPath)) {
            unlink($controllerPath);
        }

        // Esecuzione del comando
        Artisan::call('mifra:createcrud', [
            // Qui potresti dover passare eventuali argomenti o opzioni del tuo comando
            'name' => $controllerName, // Assicurati che il tuo comando accetti questo argomento
        ]);

        // Verifica: Controlla che il file del controller sia stato creato
        $this->assertTrue(File::exists($controllerPath));

        // Pulizia: Rimuovi il file del controller dopo il test
        unlink($controllerPath);
    }
}
