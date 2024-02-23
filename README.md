### Pacchetto per la creazioni di nuovi CRUD in modo automatico

Per il funzionamento del pacchetto sul tuo progetto laravel sono richieste queste dipendenze da installare:

`composer require mongodb/laravel-mongodb`
`composer require spatie/laravel-permission`

###### Installazione pacchetto

`composer require mifra/crud`

Aggiungi le seguenti variabili al tuo file .env di laravel:

```
MIFRA_TEMPLATE_PATH=views/template

MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=myDatabase
MONGODB_COLLECTION=myCollection
MONGODB_USERNAME=myUsername
MONGODB_PASSWORD=myPassword
```

E lanciare questo comando per installare e creare i CRUD principali:

`php artisan mifra:install`

###### Comando per pubblicare il file di configurazione

`php artisan vendor:publish --provider="Mifra\Crud\MifraCrudServiceProvider"`

##### Comando per creare una versione del pacchetto specifica:

`git tag -a v1.0.0 -m "Creazione versione 1.0.0"`
`git push origin v1.0.0`