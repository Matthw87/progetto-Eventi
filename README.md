# MARION CMS 
## Inizializzazione progetto 

```bash
    marion init [project_name]
```

## Clonare un progetto esistente
```bash
    git clone https://repository_url/[project_name]
    cd [project_name] 
    cp env.example .env
    marion up -d
    marion composer:install
    marion command migrate
    marion setup
```
## CHANGE LOGS
##3.1.1
Rimosse librerie js inutilizzate
##3.1.0
Aggiunto comando per forzare download modulo già scaricato: module require [module_name] --force-download
##3.0.4
Bugfix PageComposerComponent class compatibility php 8.1
##3.0.3
Bugfix Form class compatibility php 8.1
##3.0.2
Bugfix pagecomposer compatibility php 8.1
##3.0.1
Bugfix php 8.1
Rimossa funzione deprecata strftime
##3.0.0
Upgrade composer dependencies (php 8.1)
Add action "action_register_scss_variables"
Bugfix scss compiler
##2.7.1
Bugfix creazione utente
Bugfix background in pagecomposer widget
##2.7.0
Modificato command "setup" e pipelines per deploy
##2.6.0
##2.6.1
Bugfix pagecomposer
##2.6.0
Aggiunto hook "action_override_set_language" per effettuare l'override del settaggio della lingua in fase di inizializzazione dell'applicazione
Modificato metodo Marion::setUser(). Ora è possibile settare l'utente corrente senza memorizzarlo in sessione
Installata dipendenza illuminate/pagination
##2.5.0
Bugfix creazione modulo da command line
Installata dipendenza zircote/swagger-php
##2.4.0
Aggiunta opzione per cambiare il prefisso di una rotta per quelle definite nei moduli anche per i methody "match" e "any"
Bugfix routing nei moduli di tipo tema
##2.3.0
Aggiunta opzione per cambiare il prefisso di una rotta per quelle definite nei moduli
##2.2.1
Bugfix gestione widget form (buildFilemanager) per campi multilingua
##2.2.0
Aggiunta possibilità di avviare i seeder dei moduli nel commando "setup"
##2.1.2
Bugfix in comando setup
##2.1.1
Bugfix in creazione nuova homepage
##2.1.0
Aggiunta funzionalità per estendere una classe esistente
bugfix in command per upgrade tema
##2.0.3
bugfix in resource LinkMenuFrontend: convertito il campo image da intero a stringa
##2.0.2
bugfix in command per installazione e disinstallazione temi
##2.0.1
bugfix in command "ModuleRequireCommand"