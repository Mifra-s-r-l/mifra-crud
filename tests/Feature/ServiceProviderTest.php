<?php

namespace Mifra\Crud\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Mifra\Crud\MifraCrudServiceProvider;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        // Registrare il ServiceProvider all'interno dell'ambiente di test Testbench
        return [MifraCrudServiceProvider::class];
    }

    /** @test */
    public function it_registers_config()
    {
        // Caricare una configurazione di prova per verificare che sia stata registrata
        $value = config('mifracrud.template_path');

        $this->assertEquals('views/template', $value);
    }
}
