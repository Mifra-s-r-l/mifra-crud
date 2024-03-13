<?php

namespace Mifra\Crud\Helpers;

use Illuminate\Support\Facades\File;

class CrudHelpers
{
    public static function conversionRouteName($routeName, $returnType)
    {
        switch ($returnType) {
            case "permission":
                return str_replace(".", "_", $routeName);
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

    public static function createControllerFile($commands, $route_name, $directoryPathController, $fileStub = "CrudController")
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPathController)) {
            File::makeDirectory($directoryPathController, 0755, true);
        }

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/'.$fileStub.'.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $controllerTemplate = File::get($stubPath);
        $controllerContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $controllerTemplate);

        $controllerFilePath = base_path($directoryPathController . "/{$className}Controller.php");
        File::put($controllerFilePath, $controllerContent);

        $commands->info("Creato/Aggiornato il controller: ".$controllerFilePath.", qui puoi inserire il tuo codice per gestire la logica della vista");
    }

    public static function createRequestFile($commands, $route_name, $directoryPathRequest, $fileStub = "CrudRequest")
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPathRequest)) {
            File::makeDirectory($directoryPathRequest, 0755, true);
        }

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/'.$fileStub.'.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $requestTemplate = File::get($stubPath);
        $requestContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $requestTemplate);

        $requestFilePath = base_path($directoryPathRequest . "/{$className}Request.php");
        File::put($requestFilePath, $requestContent);

        $commands->info("Creato/Aggiornato il request: ".$requestFilePath.", qui puoi inserire la logica delle request");
    }

    public static function createFile($commands, $fileName, $directoryPath, $fileStub, $msg)
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/'.$fileStub.'.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $fileTemplate = File::get($stubPath);
        $filePath = base_path($directoryPath . "/{$fileName}.php");
        File::put($filePath, $fileTemplate);

        $commands->info("Creato/Aggiornato il file: ".$filePath." ".$msg);
    }

    public static function createModelFile($commands, $route_name, $directoryPathModel, $fileStub = "CrudModel")
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPathModel)) {
            File::makeDirectory($directoryPathModel, 0755, true);
        }

        $stubPath = __DIR__ . '/../resources/stubs/'.$fileStub.'.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $modelTemplate = File::get($stubPath);
        $modelContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $modelTemplate);

        $modelFilePath = base_path($directoryPathModel . "/{$className}Model.php");
        File::put($modelFilePath, $modelContent);

        $commands->info("Creato/Aggiornato il model: ".$modelFilePath.", qui puoi inserire il tuo codice per gestire il database della vista");
    }

    public static function createViewFile($commands, $route_name)
    {
        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/index.blade.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        // Assicurati che questa directory esista o sia creata
        $path = CrudHelpers::conversionRouteName($route_name, 'path');

        $directoryPathViewCrud = base_path('resources/views/' . $path);
        if (!File::exists($directoryPathViewCrud)) {
            File::makeDirectory($directoryPathViewCrud, 0755, true);
        }

        $viewTemplate = File::get($stubPath);
        $viewContent = str_replace(['%%route_name%%', '%%path%%'], [$route_name, $path], $viewTemplate);

        $viewFilePath = $directoryPathViewCrud . "/index.blade.php";
        File::put($viewFilePath, $viewContent);

        $commands->info("Creato/Aggiornato il file view: ".$viewFilePath.", adesso basta creare il file index.blade.php in questo percorso pages/" . $path . " per la grafica della vista");
    }
}
