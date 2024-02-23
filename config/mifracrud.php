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

    'template_path' => env('MIFRA_TEMPLATE_PATH', 'views/template'),

    /*
    |--------------------------------------------------------------------------
    | Database CRUD Mongo
    |--------------------------------------------------------------------------
    |
    | Inserire qui i dati per creare il tuo database CRUD mongo
    |
     */
    
    'database' => [
        'host'          => env('MONGODB_HOST', 'localhost'),
        'port'          => env('MONGODB_PORT', 27017),
        'database'      => env('MONGODB_DATABASE', 'database'),
        'collection'    => env('MONGODB_COLLECTION', 'collection'),
        'username'      => env('MONGODB_USERNAME', 'username'),
        'password'      => env('MONGODB_PASSWORD', 'password')
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
            "route_name" => "admin.menus"
        ],
        [
            "id" => 2,
            "title" => "Utenti",
            "desc" => "Pagina per la gestione degli Utenti",
            "route_name" => "admin.users"
        ],
        [
            "id" => 3,
            "title" => "Ruoli",
            "desc" => "Pagina per la gestione dei Ruoli",
            "route_name" => "admin.roles"
        ],
        [
            "id" => 4,
            "title" => "Permessi",
            "desc" => "Pagina per la gestione dei Permessi",
            "route_name" => "admin.permissions"
        ]
    ]
    
];
