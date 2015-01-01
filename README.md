####Boilerplate pour applis 100% client ou PHP avec SlimFramework + RedbeanPHP.

Pour faire une appli PHP avec Slim, supprimer le fichier `public/index.html`. Ne pas oublier de lancer `php composer.phar install` pour installer slim et redbean.

Pour faire une appli sans PHP, supprimer `app` et `public/index.php`, mettre le contenu du dossier `public` à la racine et bien définir `withPHP = false` en haut du fichier `Gruntfile.js`.

Dans tous les cas, ne pas oublier de lancer `bower install` pour installer les dépendances JavaScript par défaut.