<p align="center"><a href="https://www.mifra.com" target="_blank"><img src="https://www.mifra.eu/images/Logo_mifra_10anni.png" width="150" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://www.mifra.eu"><img src="https://img.shields.io/badge/version-1.0.x--dev-blue" alt="Versione"></a>
</p>

### Pacchetto per la creazioni di nuovi CRUD in modo automatico

Per il funzionamento del pacchetto sul tuo progetto laravel sono richieste queste dipendenze da installare:

`composer require mongodb/laravel-mongodb`
`composer require spatie/laravel-permission`

###### Installazione pacchetto

`composer require mifra/crud`

Aggiungi le seguenti variabili al tuo file .env di laravel:

```
MIFRACRUD_TEMPLATE_PATH=views/template

MIFRACRUD_MONGODB_HOST=127.0.0.1
MIFRACRUD_MONGODB_PORT=27017
MIFRACRUD_MONGODB_DATABASE=myDatabase
MIFRACRUD_MONGODB_COLLECTION=myCollection
MIFRACRUD_MONGODB_USERNAME=myUsername
MIFRACRUD_MONGODB_PASSWORD=myPassword
```

E lanciare questo comando per installare e creare i CRUD principali:

`php artisan mifra:install`

- una volta installato il pacchetto basta creare le voci di menu come indicato durante l'installazione

Si informa che lanciando questo comando tutti i file verrano ricreati e azzerati ma il database rimarrà invariato (prossimamente inseriremo qualcosa per evitare questo)



###### Comando per pubblicare il file di configurazione

`php artisan vendor:publish --provider="Mifra\Crud\MifraCrudServiceProvider"`

Importante:
- se viene modificata la lista per funzionare e importate che il parametro "route_name" mantenga il nome.nome per delimitare perchè viene usato nell'installazione per creare la struttura del template nella cartella resources

##### Comando per creare una versione del pacchetto specifica:

`git tag -a v1.0.0 -m "Creazione versione 1.0.0"`
`git push origin v1.0.0`