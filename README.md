# Wamcar

[![pipeline status](https://gitlab.novaway.net/novaproject/wamcar/badges/develop/pipeline.svg)](https://gitlab.novaway.net/novaproject/wamcar/commits/develop)

Wamcar est un portail propsant la mise en relation de particulier souhaitant changer de voiture avec des professionnel pouvant leur faire une offre de reprise.

## Architecture

Une architecture serveur de développement a été mise en place via des containers docker (voir plus bas pour son utilisation).

Coté back l'outil est [développé en PHP 7](composer.json) sur le [framework Symfony](app/AppKernel.php).

Coté front, le développement est [centralisé via NPM + Webpack 2](package.json). Le code javascript suit le standard ES2017 (transpilé par Babel) et style est étendu par SASS.

### Variables d'environnement

Dans une optique de respect des recommandation du manifeste _[12 Factor App](https://12factor.net/config)_, la configuration de l'application propre à l'environnement est stockée sous la forme de variables d'environnement.

Pour le développement, ces variables d'environement [sont pré-remplies](.env) et chargées à l'utilisation du contrôleur `app_dev.php`.

### Makefile

[Un fichier `Makefile`](Makefile) centralise l'ensemble des commandes fréquentes pour le développement de l'outil. La commande `make` seule permet de lister les commandes make disponibles.

### API externes

> TODO: en attente d'info client


## Setup d'un environnement de développement

Une [configuration docker centralisée](docker-compose.yml) est prévue pour être utilisée pour le développement de l'outil. Elle n'est pas indispensable et peut être replacée par des outils locaux ou des VMs. Ce guide se base sur cette configuration.

### Configuration des variables d'environnement

Le fichier `.env` contient les variables d'environnement utilisée pour la configuration des images docker. Néanmoins, il peut être nécessaire d'avoir à les modifier, par exemple pour éviter les conflits de ports entre 2 containers sur une machine de développement.
  
Il est donc judicieux d'avoir lu ce fichier avant de commencer l'installation.

### Ajout d'un host local

Afin de travailler avec des domaines plutôt que des IP (plus souple pour ce qui est routing, configuration de tests, ...), il est recommandé de créer la configuration suivante dans le fichier `/etc/hosts` de la machine de développement :

```
127.0.0.1        wamcar.local
## et/ou
127.0.0.1        myapp.local
```

### Utilisation du registry Novaway

Certaines images dockers sont hébergé sur le registry interne à Novaway. Il s'authentifier pour les utiliser.

```
$ docker login registry-gitlab.novaway.net
```

> **Note** : Pour utiliser s'authentifier avec l'option 2FA d'activée, il faut passer par [un jeton d'accès perso](https://gitlab.novaway.net/help/user/profile/account/two_factor_authentication#personal-access-tokens)

### Installation automatique

```sh
$ make dev
```

Cette commande permet de lancer l'installation du projet. Elle peut être réutilisée au cours du développement, notamment après un pull des sources pour mettre à jour les vendors back et les assets front.

## Developpement front

Pour le dev front, watchers sont à disposition.

Le premier watcher, lancée via la commande `make front-start` charge webpack de surveiller les assets et de les compiler en version "développement"

Le second, lancé via `make front-live` lance [BrowserSync](https://browsersync.io/) pour gérer le live reloading et le miroring multi appareils.

> Todo : essayer de concaténer les 2 en 1 seul watcher et donc une seule commande

## Tests

L'application est testée de façon unitaire et comportementale. La commande suivante permet de lancer la suite complete, en créant à la volée les dépendances nécessaires (services docker, assets ... ) :

```sh
$ make test
```

### Tests unitaires

#### Backend

Le code PHP est testé unitairement avec l'outil Atoum. La suite de test se lance avec la commande

```sh
make test-unit
```

#### Frontend

Le frontend n'est pas actuellement testé

### Test comportementaux

Le comportement de l'application est testée de façon comportale avec Behat.

```sh
make test-behavior
```

2 drivers sont disponibles pour le lancement des tests :

- *goutte*, utilisé par défaut ;
- *selenium2*, utilisé si le scénario gherkin du test est annoté par le tag `@javascript`


### Rapports de tests

Les tests génèrent des rapports au format xunit contenus dans le répertoire `build` du projet.

> Todo : Utiliser un format plus human-friendly

## Déploiement

### Recette interne

> Todo

### Recette client

> Todo

### Production

> Todo

