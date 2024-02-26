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
        'host'          => env('MIFRACRUD_MONGODB_HOST', 'localhost'),
        'port'          => env('MIFRACRUD_MONGODB_PORT', 27017),
        'database'      => env('MIFRACRUD_MONGODB_DATABASE', 'database'),
        'collection'    => env('MIFRACRUD_MONGODB_COLLECTION', 'collection'),
        'username'      => env('MIFRACRUD_MONGODB_USERNAME', 'username'),
        'password'      => env('MIFRACRUD_MONGODB_PASSWORD', 'password')
    ],

    /*
    |--------------------------------------------------------------------------
    | Menù di default
    |--------------------------------------------------------------------------
    |
    | Inserire qui i dati per creare il menù di defaul in fase di installazione
    |
     */
    
    'menus' => [
        [
            "id" => 1,
            "title" => "Crud",
            "desc" => "Pagina per la gestione dei CRUD",
            "path" => "mifracruds/menus",
            "crud_name" => "MifraCrudMenus",
            "route_name" => "mifracruds.menus"
        ],
        [
            "id" => 2,
            "title" => "Utenti",
            "desc" => "Pagina per la gestione degli Utenti",
            "path" => "mifracruds/users",
            "crud_name" => "MifraCrudMenus",
            "route_name" => "mifracruds.users"
        ],
        [
            "id" => 3,
            "title" => "Ruoli",
            "desc" => "Pagina per la gestione dei Ruoli",
            "path" => "mifracruds/roles",
            "crud_name" => "MifraCrudMenus",
            "route_name" => "mifracruds.roles"
        ],
        [
            "id" => 4,
            "title" => "Permessi",
            "desc" => "Pagina per la gestione dei Permessi",
            "path" => "mifracruds/permissions",
            "crud_name" => "MifraCrudMenus",
            "route_name" => "mifracruds.permissions"
        ]
    ]
    
];
