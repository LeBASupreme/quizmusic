# QuizMusic

Un petit projet pédagogique en PHP procédural qui propose des questionnaires musicaux thématiques (Rock, Pop française, Rap US, Électro, Disney...). Ce dépôt contient une page d'accueil dynamique (`index.php`) qui présente les quiz et permet de lancer un thème.

## Fonctionnalités
- Présentation des quiz sous forme de cartes dynamiques
- Gestion simple des thèmes via un tableau associatif PHP
- Exemple d'utilisation des sessions, fonctions et boucles en PHP procédural
- Mise en page moderne avec Tailwind CSS (CDN)

## Structure du projet
- `index.php` : page d'accueil et liste des questionnaires (fichier fourni)
- `quiz.php` : (à créer) page du quiz qui traitera `$_GET['theme']`
- `style.css` : styles additionnels (optionnel)
- `README.md` : documentation (ce fichier)

> Remarque : pour l'instant le dépôt contient `index.php`. Vous pouvez ajouter `quiz.php` et d'autres ressources au fur et à mesure.

## Prérequis
- PHP 7.4+ installé localement
- Navigateur web moderne

## Exécution locale rapide
Ouvrez un terminal dans le dossier du projet et lancez le serveur web intégré de PHP :

```bash
# depuis la racine du projet
php -S localhost:8000 -t .
```

Ensuite ouvrez `http://localhost:8000/index.php` dans votre navigateur.

## Conseils pratiques
- Ajouter un fichier `.gitignore` pour ignorer `vendor/`, fichiers de configuration locaux, et les clés/secret si vous en créez.
- Créer `quiz.php` pour récupérer `$_GET['theme']`, valider l'entrée, charger les questions et afficher le formulaire de réponses.
- Centraliser les données (questions) dans un fichier PHP ou JSON séparé pour faciliter l'extension.

## Contribution
Contributions bienvenues : ouvrez une issue ou une pull request. Pour des changements majeurs, créez d'abord une branche feature/xxx.

## Licence
Ce projet peut être placé sous la licence de votre choix (ex : MIT). Ajoutez un fichier `LICENSE` si vous souhaitez en appliquer une.

