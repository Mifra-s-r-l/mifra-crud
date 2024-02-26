<?php

namespace Tests\Feature;

use Mifra\Crud\MifraCrudServiceProvider;
use Orchestra\Testbench\TestCase;

class WebRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $menuItems = config('mifracrud.menus'); // Voci di menu del file di config
        foreach ($menuItems as $menuItem) {
            // Definire le rotte direttamente
            $this->app['router']->get( str_replace(".", "/", $menuItem['route_name']), function () {
                return 'Menu Page';
            })->name($menuItem['route_name']);
        }
    }

    protected function getPackageProviders($app)
    {
        // Registrare il ServiceProvider all'interno dell'ambiente di test Testbench
        return [MifraCrudServiceProvider::class];
    }

    /** @test */
    public function menus_page_can_be_accessed()
    {
        $menuItems = config('mifracrud.menus'); // Voci di menu del file di config
        $result = "";

        foreach ($menuItems as $menuItem) {

            $response = $this->get(route($menuItem['route_name']));

            $response->assertStatus(200);
        }
    }
}
