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
            "path" => "cruds/menus",
            "controller_name" => "MifraCrudMenusController",
            "route_name" => "cruds.menus"
        ],
        [
            "id" => 2,
            "title" => "Utenti",
            "desc" => "Pagina per la gestione degli Utenti",
            "path" => "cruds/users",
            "controller_name" => "MifraCrudUsersController",
            "route_name" => "cruds.users"
        ],
        [
            "id" => 3,
            "title" => "Ruoli",
            "desc" => "Pagina per la gestione dei Ruoli",
            "path" => "cruds/roles",
            "controller_name" => "MifraCrudRolesController",
            "route_name" => "cruds.roles"
        ],
        [
            "id" => 4,
            "title" => "Permessi",
            "desc" => "Pagina per la gestione dei Permessi",
            "path" => "cruds/permissions",
            "controller_name" => "MifraCrudPermissionsController",
            "route_name" => "cruds.permissions"
        ]
    ]
    
];
