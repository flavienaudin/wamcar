# Wamcar

Wamcar est un outil en ligne pour la gestion de production vidéos, spécialisé dans le domaine de l'autmobile.

## Architecture

Coté back l'outil est [développé en PHP 7](composer.json) sur le [framework Symfony](app/AppKernel.php).

Coté front, le développement est [centralisé via NPM + Webpack 2](package.json). Le code javascript suit le standard ES2017 (transpilé par Babel) et style est étendu par SASS.

### Variables d'environnement

Dans une optique de respect des recommandation du manifeste _[12 Factor App](https://12factor.net/config)_, la configuration de l'application propre à l'environnement est stockée sous la forme de variables d'environnement.

Pour le développement, ces variables d'environement [sont pré-remplies](.env) et chargées à l'utilisation du contrôleur `app_dev.php`.

Le fichier `.env` contient les variables d'environnement utilisées pour la configuration du serveur local. 

### Makefile

Consulter le fichier [Makefile](Makefile) pour connaître les commandes à exécuter pour mettre à jour des paquets ou reconstruire les fichiers 'front',... 

### Front building

- `npm run build` permet de compiler les fichiers JS et CSS en un seul fichier, à utiliser pour la production.
- `npm run start` permet de compiler à la volée (mise à jour automatique) les fichiers JS et CSS en un seul fichier, pour le développement.
 
Voir aussi la section `scripts` du fichier [package](package.json) ou le fichier [Makefile](Makefile) . 


### Configuration des variables d'environnement



### Ajout d'un host local

Afin de travailler avec des domaines plutôt que des IP (plus souple pour ce qui est routing, configuration de tests, ...), il est recommandé de créer la configuration suivante dans le fichier `/etc/hosts` de la machine de développement :

```
127.0.0.1        wamcar.local.fr
```

#### Frontend

Le frontend n'est pas actuellement testé


### Production

Liste des commandes `php bin/console`
 
