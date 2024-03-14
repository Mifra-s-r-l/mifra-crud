<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Template path
    |--------------------------------------------------------------------------
    |
    | Inserire qui il path della cartella del template che state utilizzando
    |
     */

    'template_path' => env('MIFRACRUD_TEMPLATE_PATH', 'views/template'),

    /*
    |--------------------------------------------------------------------------
    | Database CRUD Mongo
    |--------------------------------------------------------------------------
    |
    | Inserire qui i dati per creare il tuo database CRUD mongo
    |
     */

    'database' => [
        'host' => env('MIFRACRUD_MONGODB_HOST', 'localhost'),
        'port' => env('MIFRACRUD_MONGODB_PORT', 27017),
        'database' => env('MIFRACRUD_MONGODB_DATABASE', 'database'),
        'collection' => env('MIFRACRUD_MONGODB_COLLECTION', 'collection'),
        'group' => env('MIFRACRUD_MONGODB_GROUP', 'group'),
        'username' => env('MIFRACRUD_MONGODB_USERNAME', 'username'),
        'password' => env('MIFRACRUD_MONGODB_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gruppi Menù di default
    |--------------------------------------------------------------------------
    |
    | Inserire qui i dati per creare il menù di defaul in fase di installazione
    |
     */

    'groups_menus' => [
        [
            "id" => 1,
            "title" => "Gestione",
            "icon" => "Settings",
            "group" => "managements", // deve corrispondere a quello delle voci di menu
            "order" => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permessi di default
    |--------------------------------------------------------------------------
    |
    | Inserire qui che verranno creati per i nuovi CRUD
    |
     */

    "permissions" => [
        "view",
        "create",
        "modify",
        "trash",
        "delete",
    ],

    /*
    |--------------------------------------------------------------------------
    | Menù di default
    |--------------------------------------------------------------------------
    |
    | Inserire qui i dati per creare il menù di defaul in fase di installazione
    | Importate non cambiare l'orinamento il loro route_name
    |
     */

    'menus' => [
        [
            "id" => 1,
            "title" => "Crud",
            "icon" => "List",
            "desc" => "Pagina per la gestione dei CRUD",
            "route_name" => "mifracruds.cruds", // da non modificare
            "group" => "managements", // deve corrispondere a quello del gruppo
            "order" => 1,
            "permissions" => [
                "view",
                "create",
                "modify",
                "delete",
            ],
        ],
        [
            "id" => 2,
            "title" => "Utenti",
            "icon" => "Users",
            "desc" => "Pagina per la gestione degli Utenti",
            "route_name" => "mifracruds.users", // da non modificare
            "group" => "managements", // deve corrispondere a quello del gruppo
            "order" => 2,
            "permissions" => [
                "view",
                "create",
                "modify",
                "trash",
                "delete",
                "restore",
            ],
        ],
        [
            "id" => 3,
            "title" => "Ruoli",
            "icon" => "UserCog",
            "desc" => "Pagina per la gestione dei Ruoli",
            "route_name" => "mifracruds.roles", // da non modificare
            "group" => "managements", // deve corrispondere a quello del gruppo
            "order" => 3,
            "permissions" => [
                "view",
                "create",
                "modify",
                "trash",
                "delete",
            ],
        ],
        [
            "id" => 4,
            "title" => "Permessi",
            "icon" => "UserCog",
            "desc" => "Pagina per la gestione dei Permessi",
            "route_name" => "mifracruds.permissions", // da non modificare
            "group" => "managements", // deve corrispondere a quello del gruppo
            "order" => 4,
            "permissions" => [
                "view",
                "create",
                "modify",
                "trash",
                "delete",
            ],
        ],
    ],

];
