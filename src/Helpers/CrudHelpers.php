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
        $stubPath = __DIR__ . '/../resources/stubs/' . $fileStub . '.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $controllerTemplate = File::get($stubPath);
        $controllerContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $controllerTemplate);

        $controllerFilePath = base_path($directoryPathController . "/{$className}Controller.php");
        File::put($controllerFilePath, $controllerContent);

        $commands->info("Creato/Aggiornato il controller: " . $controllerFilePath . ", qui puoi inserire il tuo codice per gestire la logica della vista");
    }

    public static function createRequestFile($commands, $route_name, $directoryPathRequest, $fileStub = "CrudRequest")
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPathRequest)) {
            File::makeDirectory($directoryPathRequest, 0755, true);
        }

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/' . $fileStub . '.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $requestTemplate = File::get($stubPath);
        $requestContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $requestTemplate);

        $requestFilePath = base_path($directoryPathRequest . "/{$className}Request.php");
        File::put($requestFilePath, $requestContent);

        $commands->info("Creato/Aggiornato il request: " . $requestFilePath . ", qui puoi inserire la logica delle request");
    }

    public static function createFile($commands, $fileName, $directoryPath, $fileStub, $msg)
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }

        // Costruisci il percorso del file .stub
        $stubPath = __DIR__ . '/../resources/stubs/' . $fileStub . '.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $fileTemplate = File::get($stubPath);
        $filePath = base_path($directoryPath . "/{$fileName}.php");
        File::put($filePath, $fileTemplate);

        $commands->info("Creato/Aggiornato il file: " . $filePath . " " . $msg);
    }

    public static function createModelFile($commands, $route_name, $directoryPathModel, $fileStub = "CrudModel")
    {
        // Assicurati che questa directory esista o sia creata
        if (!File::exists($directoryPathModel)) {
            File::makeDirectory($directoryPathModel, 0755, true);
        }

        $stubPath = __DIR__ . '/../resources/stubs/' . $fileStub . '.stub';

        if (!file_exists($stubPath)) {
            $commands->error("Il file stub {$stubPath} non esiste.");
            return 1;
        }

        $className = CrudHelpers::conversionRouteName($route_name, 'className');

        $modelTemplate = File::get($stubPath);
        $modelContent = str_replace(['{{crud_name}}', '{{route_name}}'], [$className, $route_name], $modelTemplate);

        $modelFilePath = base_path($directoryPathModel . "/{$className}Model.php");
        File::put($modelFilePath, $modelContent);

        $commands->info("Creato/Aggiornato il model: " . $modelFilePath . ", qui puoi inserire il tuo codice per gestire il database della vista");
    }

    public static function modifyMiddlewareSpatie($variableMiddleware, $action) {
        $filePath = base_path('app/Http/Kernel.php'); // Percorso del file da modificare
        
        // Middleware da gestire
        $middlewaresToAdd = [
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ];
    
        // Leggi il contenuto del file
        $fileContent = file_get_contents($filePath);
    
        // Espressione regolare per trovare l'array di middleware
        $pattern = '/protected\s+\$'.$variableMiddleware.'\s*=\s*\[(.*?)\];/s';
    
        if (preg_match($pattern, $fileContent, $matches)) {
            // Estrai il contenuto dell'array di middleware
            $middlewareArrayContent = $matches[1];
    
            foreach ($middlewaresToAdd as $key => $class) {
                $middlewareLine = "'$key' => $class::class,";
    
                if ($action === 'add') {
                    // Controlla se il middleware specifico è già presente per evitare duplicati
                    if (!str_contains($middlewareArrayContent, $middlewareLine)) {
                        // Aggiungi il middleware all'array
                        $middlewareArrayContent .= "\n        " . $middlewareLine;
                    }
                } elseif ($action === 'remove') {
                    // Rimuovi il middleware dall'array se presente
                    $middlewareArrayContent = str_replace("\n        " . $middlewareLine, '', $middlewareArrayContent);
                }
            }
    
            // Ricostruisci il contenuto del file con l'array di middleware modificato
            $newFileContent = preg_replace($pattern, 'protected $'.$variableMiddleware.' = ['.$middlewareArrayContent."\n    ];", $fileContent);
    
            // Salva le modifiche nel file
            file_put_contents($filePath, $newFileContent);
        }
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

        $commands->info("Creato/Aggiornato il file view: " . $viewFilePath . ", adesso basta creare il file index.blade.php in questo percorso pages/" . $path . " per la grafica della vista");
    }

    public static function insertActionableToModelUser($filePathUser, $action)
    {
        if ($action == 'add') {
            $traitToAddOutside = "use App\Traits\MifraCruds\MifracrudsActionable;"; // Trait da aggiungere all'esterno
            $traitToAddInside = "use MifracrudsActionable;"; // Trait da aggiungere all'interno della classe

            // Leggi il contenuto del file
            $fileContent = file_get_contents($filePathUser);

            // Aggiungi il trait all'esterno della classe se non è già presente
            if (!str_contains($fileContent, trim($traitToAddOutside))) {
                // Utilizza un'espressione regolare per trovare la posizione dopo la dichiarazione del namespace
                $patternNamespace = '/namespace\s+[^;]+;/';
                preg_match($patternNamespace, $fileContent, $matches, PREG_OFFSET_CAPTURE);

                if (!empty($matches)) {
                    // Calcola la posizione di inserimento subito dopo il namespace
                    $insertPosition = $matches[0][1] + strlen($matches[0][0]) + 1; // Aggiungi 1 per andare a capo dopo il namespace
                    $fileContent = substr_replace($fileContent, "\n" . $traitToAddOutside, $insertPosition, 0);
                } else {
                    // Se non c'è un namespace, aggiungi il `use` all'inizio del file.
                    $fileContent = "<?php\n\n" . $traitToAddOutside . substr($fileContent, 5);
                }
            }

            // Utilizza le espressioni regolari per trovare la dichiarazione della classe e aggiungere il trait all'interno
            if (!str_contains($fileContent, $traitToAddInside)) {
                $pattern = '/class\s+\w+\s+extends\s+\w+\s*\{/'; // Pattern per identificare la dichiarazione della classe
                $replacement = "\$0\n    $traitToAddInside"; // Aggiungi il trait all'interno della classe
                $fileContent = preg_replace($pattern, $replacement, $fileContent, 1);
            }

            // Salva le modifiche nel file
            file_put_contents($filePathUser, $fileContent);
        }
        if ($action == 'remove') {
            $contentModelUser = File::get($filePathUser);
            $updatedContentModelUser = str_replace(["\n    use MifracrudsActionable;", "\nuse App\Traits\MifraCruds\MifracrudsActionable;"], ["", ""], $contentModelUser);
            File::put($filePathUser, $updatedContentModelUser);
        }
    }
}
