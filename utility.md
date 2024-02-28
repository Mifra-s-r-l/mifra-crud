##### Per eseguire PHPUnit e lanciare i tuoi test, puoi utilizzare il seguente comando nel terminale, assicurandoti di essere nella directory radice del tuo progetto Laravel:

`./vendor/bin/phpunit`

##### Ecco come puoi fare per aggiornare la cache di Git e applicare le modifiche al .gitignore:

```
git rm -r --cached .
git add .
git commit -m "Aggiornata la cache per rispettare .gitignore"
```

##### Comando per creare una versione del pacchetto specifica:

`git tag -a v1.0.0 -m "Creazione versione 1.0.0"`
`git push origin v1.0.0`