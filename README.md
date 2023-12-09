# Syme

*L'autre micro-framework PHP.*

**Syme** est un petit framework PHP aidant à la réalisation d'applications Web. Simple et rapide, ce framework a été conçu pour accélérer le développement tout en restant modulaire et efficace. Applications Web, API Rest, Syme est l'outil incontournable de vos projets.


> **Documentation** (*en cours...*):
> 
> https://pantaflex44.gitbook.io/documentation-de-syme/


> **Installation des dépendances**:
>   ```bash
>   $ composer install
>   ```

> **Edition des configurations**:
>   ```bash
>   $ sudo nano config.php
>   ```


### Exemple d'utilisation:

```php
<?php

declare(strict_types=1);

require_once './core.php';


use components\Data;
use components\Request;
use components\Response;
use components\Route;


Route::get('home', '/', function (Response $response): Response {
    $response
        ->write("Bonjour le monde");

    return $response;
});

Route::get('article', '/article/{id:[0-9]+}/author/{name}', function (int $id, string $name, Response $response, Data $data): Response {
    $obj = ['article id' => $id, 'author name' => $name];
    $obj = array_merge($obj, $data->all());

    $response
        ->writeObject($obj)
        ->withStatus(200);

    return $response;
});
Route::before('article', function(Request $request, Response $response, Data $data): void {
    $data->set('who am i ?', "I'm a midlleware ;-)");
});
```


## Les extensions facultatives:

### Composants disponibles (/components/extended)

- **Session** : Gestionnaire de sessions paramètrable.
- **MySQL** : Gestionnaire de données MySQL utilisant PDO.
- **TwigWrapper** : Permet l'utilisation de ```Twig``` via Syme. Requiert ```twig/twig:^3.0``` via Composer.


### Middlewares disponibles (/middlewares)

- **CsrfMiddleware** : Permet d'ajouter une protection contre les attaques CSRF aux formulaires HTML.



<br /><br />
<div style="text-align: center;">

![Syme](./Syme.png)

</div>
<br />