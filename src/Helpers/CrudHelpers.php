<?php

namespace Mifra\Crud\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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

    public static function convertPathToNamespace($filePath)
    {
        // Rimuovi l'estensione .php
        $withoutExtension = preg_replace('/\.php$/', '', $filePath);

        // Sostituisci i separatori di directory con backslash e capitalizza ogni segmento
        $segments = explode('/', $withoutExtension);
        $capitalizedSegments = array_map(function ($segment) {
            return ucfirst($segment);
        }, $segments);
        $namespace = implode('\\', $capitalizedSegments);

        return $namespace;
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

    public static function modifyMiddlewareSpatie($variableMiddleware, $action)
    {
        $filePath = base_path('app/Http/Kernel.php'); // Percorso del file

        // Middleware da gestire
        $middlewaresToAdd = [
            'role' => '\\Spatie\\Permission\\Middleware\\RoleMiddleware::class',
            'permission' => '\\Spatie\\Permission\\Middleware\\PermissionMiddleware::class',
            'role_or_permission' => '\\Spatie\\Permission\\Middleware\\RoleOrPermissionMiddleware::class',
        ];

        // Leggi il contenuto del file
        $fileContent = file_get_contents($filePath);

        // Trova l'array di middleware
        $pattern = '/protected\s+\$' . $variableMiddleware . '\s*=\s*\[(.*?)\];/s';

        if (preg_match($pattern, $fileContent, $matches)) {
            $middlewareArrayContent = $matches[1];

            if ($action === 'add') {
                $middlewareArrayContent .= "\n        // Spatie Permission di MifraCruds\n\n    ";
            }

            foreach ($middlewaresToAdd as $key => $class) {
                $middlewareLine = "'$key' => $class,";

                if ($action === 'add') {
                    // Aggiungi solo se non già presente
                    if (!str_contains($middlewareArrayContent, $middlewareLine)) {
                        // Controlla l'ultimo carattere per evitare spazi extra
                        $middlewareArrayContent .= "    " . $middlewareLine . "\n    ";
                    }
                } elseif ($action === 'remove') {
                    // Rimuovi il middleware se presente
                    $middlewareToRemove = "\n        " . $middlewareLine;
                    $middlewareArrayContent = str_replace($middlewareToRemove, '', $middlewareArrayContent);
                }
            }

            if ($action === 'remove') {
                $middlewareArrayContent = str_replace("\n        // Spatie Permission di MifraCruds\n\n    ", '', $middlewareArrayContent);
            }

            // Ricostruisci e sostituisci il contenuto dell'array modificato
            $newFileContent = preg_replace($pattern, 'protected $' . $variableMiddleware . ' = [' . $middlewareArrayContent . "];", $fileContent);

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
        $filePath = base_path($filePathUser);
        $namespace = CrudHelpers::convertPathToNamespace($filePathUser);
        $alreadyInstalledFlagPath = base_path('mifra_crud_installed.json');

        if (File::exists($alreadyInstalledFlagPath)) {
            // 1. Leggi il contenuto del file JSON
            $jsonContent = File::get($alreadyInstalledFlagPath);
            // 2. Decodifica la stringa JSON in un array PHP
            $array = json_decode($jsonContent, true);
        }

        if ($action == 'add') {
            $traitToAddOutside = "use App\Traits\MifraCruds\MifracrudsActionable;"; // Trait da aggiungere all'esterno
            $traitToAddInside = "use MifracrudsActionable;"; // Trait da aggiungere all'interno della classe

            // Leggi il contenuto del file
            $fileContent = file_get_contents($filePath);

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
            file_put_contents($filePath, $fileContent);

            if (isset($array['id_admin'])) {
                $namespace::where('id', $array['id_admin'])->forceDelete();
            }

            // Creo il ruolo super-admin se non è presente e l'utente admin di default
            Role::firstOrCreate([
                'name' => 'super-admin',
            ]);
            $admin = $namespace::factory()->create([
                'name' => 'Super Admin',
                'email' => 'admin@admin.it',
                'email_verified_at' => now(),
                'password' => Hash::make('admin'),
                'remember_token' => Str::random(30),
            ]);
            $admin->assignRole(array("super-admin"));

            // 3. Aggiungi `id_admin=1` all'array
            $array['id_admin'] = $admin->id;
            // 4. Codifica l'array modificato in JSON
            $jsonData = json_encode($array);
            // 5. Salva la nuova stringa JSON nel file
            File::put($alreadyInstalledFlagPath, $jsonData);
        }
        if ($action == 'remove') {
            $contentModelUser = File::get($filePath);
            $updatedContentModelUser = str_replace(["\n    use MifracrudsActionable;", "\nuse App\Traits\MifraCruds\MifracrudsActionable;"], ["", ""], $contentModelUser);
            File::put($filePath, $updatedContentModelUser);

            if (isset($array['id_admin'])) {
                $superAdmin = $namespace::where('id', $array['id_admin'])->withTrashed()->first();
                $superAdmin->roles()->detach();
                $superAdmin>forceDelete();
            }
        }
    }

    public static function updateComposer()
    {
        //TODO creare un array che si riempe ogni volta che viene creato un file e salvarlo
        // su mifra_crud_installed.json da confrontare nelle future installazioni/update in modo da cancellare i file orfani
    }
}
