# Les assets

Dans un projet web, il est souvent nécessaire de servir différents types de fichiers aux visiteurs. Que ce soit des images, icônes ou tous autres documents, un accès public est rendu disponible par **Syme**.

Pour ce faire, un dossier `/public` est disponible à la racine de l'application. Ce dossier a la faculté de pouvoir être "rangé" en utilisant de multiples sous-dossier.

Exemple:

```bash
\public
    \images
        \icons
            favicon.ico
        \imgs
            img1.png
            img2.png
        mon_image.png
    \css
        styles.css
    \js
        scripts.js
```

Pour y accéder via "l'extérieur", le système de routage analyse en permanence chaque URL qu'il détecte pour essayer de reconnaître un fichier public existant, au quel cas, il le renvoie au visiteur. Le cas échéant, il analyse les règles de routage connues pour aiguiller correctement l'application.

Exemple:

`https://localhost/mon_app/images/mon_image.png`

Le système de routage va essayer, en premier lieu, de trouver le fichier `mon_image.png` dans le sous-dossier `images` du dossier `public` à la racine de l'application.

`/public/images/mon_image.png`



### La compatibilité HTTP

**Syme** est compatible avec les requêtes HTTP\_RANGE permettant de servir seulement une portion d'un fichier.&#x20;

Le système d'envoie reconnait et indique automatiquement le type mime des fichiers transmis.
