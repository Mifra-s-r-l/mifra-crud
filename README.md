<p align="center"><a href="https://www.mifra.com" target="_blank"><img src="https://www.mifra.eu/images/Logo_mifra_10anni.png" width="150" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://www.mifra.eu"><img src="https://img.shields.io/badge/version-1.0.x--dev-blue" alt="Versione"></a>
</p>

## Pacchetto per la creazioni di nuovi CRUD in modo automatico

In generale, un sistema CRUD in un'applicazione Laravel, serve per la creazione di file di controller, model, e view necessari, e la configurazione delle rotte per l'accesso alle funzionalità CRUD. Questo facilita lo sviluppo e la manutenzione dell'applicazione, permettendo agli sviluppatori di concentrarsi sulla logica specifica dell'applicazione piuttosto che sulla configurazione iniziale e sul boilerplate code.

Per il funzionamento del pacchetto sul tuo progetto laravel sono richieste queste dipendenze da installare:

```composer require mongodb/laravel-mongodb```
```composer require spatie/laravel-permission```
  
  
  
  
  
## Installazione pacchetto

Aggiungi le seguenti variabili al tuo file .env di Laravel per configurare l'accesso a MongoDB e il percorso del template CRUD:

```
MIFRACRUD_TEMPLATE_PATH=views/template

MIFRACRUD_MONGODB_HOST=127.0.0.1
MIFRACRUD_MONGODB_PORT=27017
MIFRACRUD_MONGODB_DATABASE=myDatabase
MIFRACRUD_MONGODB_COLLECTION=myCollection
MIFRACRUD_MONGODB_GROUP=myGroup
MIFRACRUD_MONGODB_USERNAME=myUsername
MIFRACRUD_MONGODB_PASSWORD=myPassword
```

Prima di procedere con l'installazione del sistema CRUD, è necessario installare il pacchetto mifra/crud tramite Composer. 
Esegui il seguente comando:

`composer require mifra/crud`

Procedi con l'installazione eseguendo il comando Artisan per installare e configurare i CRUD principali:

`php artisan mifra:installcrud`

Questo comando configura automaticamente la connessione MongoDB, crea le directory necessarie per i Controllers, Models e Views del tuo CRUD, genera i file necessari basati sui template predefiniti e configura le rotte necessarie per il funzionamento del CRUD.

###### NOTE:
1. Se dovessi riscontrare problemi nella visualizzazione dei contenuti per via dei permessi, puoi provare a dare il comando:
`sudo php artisan permission:cache-reset`
2. Si raccomanda di aggiungere con migrate alla tabella users di laravel il softdeletes e il campo personalizzato di tipo json "actions" per usare il cestina e le info di chi effettua le azioni su vari elementi di una tabella
3. Prima dell'installazione del pacchetto: Assicurati di avere Composer installato e di essere connesso al tuo database MongoDB.
Dopo l'installazione del pacchetto: Potrebbe essere necessario personalizzare i file di controller, model e view generati per adattarli alle esigenze specifiche del tuo progetto.
  



  
### Reinstallazione con Sovrascrittura

Se necessario aggiornare il CRUD sovrascrivendo le configurazioni, utilizza l'opzione --update:

`php artisan mifra:installcrud --update`

Utilizzando l'opzione --update, il comando forza la reinstallazione del CRUD, sovrascrivendo qualsiasi configurazione esistente.

### Comando per pubblicare il file di configurazione

`php artisan vendor:publish --provider="Mifra\Crud\MifraCrudServiceProvider"`

###### Importante:
- se viene modificata la lista delle voci di menù principali, per un corretto funzionamento il parametro "route_name" deve rimanere invariato perchè server la creazione dei file e delle directory


### Creazione delle viste principali :

Inserire qui la guida che spiega il messaggio di avviso quando non trova le viste