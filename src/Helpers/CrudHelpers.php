<?php

namespace Mifra\Crud\Helpers;

class CrudHelpers
{
    public static function conversionRouteName($routeName, $returnType)
    {
        switch ($returnType) {
            case "path":
                return str_replace(".", "/", $routeName);
            case "className":
                $items = explode(".", $routeName);
                $result = '';
                foreach ($items as $item) {
                    $result .= ucwords($item);
                }
                return $result;
            default:
                return $routeName;
        }
    }

    public static function createControllerFile($commands, $route_name)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/CrudController.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $controllerTemplate = File::get($stubPath);
        $controllerContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $controllerTemplate);

        $controllerFilePath = $commands->directoryPathController . "/{$className}Controller.php";
        File::put($controllerFilePath, $controllerContent);

        $commands->info("Creato/Aggiornato il controller: App\Http\Controllers\MifraCruds\\{$className}Controller, qui puoi inserire il tuo codice per gestire la logica della vista");
    }
}
