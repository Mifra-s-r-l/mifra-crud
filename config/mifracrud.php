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
        'group'         => env('MIFRACRUD_MONGODB_GROUP', 'group'),
        'username'      => env('MIFRACRUD_MONGODB_USERNAME', 'username'),
        'password'      => env('MIFRACRUD_MONGODB_PASSWORD', 'password')
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
            "icon" => "settings",
            "group" => "managements"
        ],
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
            "route_name" => "mifracruds.menus",
            "group" => "managements"
        ],
        [
            "id" => 2,
            "title" => "Utenti",
            "desc" => "Pagina per la gestione degli Utenti",
            "route_name" => "mifracruds.users",
            "group" => "managements"
        ],
        [
            "id" => 3,
            "title" => "Ruoli",
            "desc" => "Pagina per la gestione dei Ruoli",
            "route_name" => "mifracruds.roles",
            "group" => "managements"
        ],
        [
            "id" => 4,
            "title" => "Permessi",
            "desc" => "Pagina per la gestione dei Permessi",
            "route_name" => "mifracruds.permissions",
            "group" => "managements"
        ]
    ]
    
];
