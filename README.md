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

    Sources: https://github.com/pantaflex44/Syme/blob/v1/components/extended/Session.php

    
    Requiert:

        - Composant: 'components\core\Route'

        - Configuration: SESSION_USE_COOKIES (facultatif)
        - Configuration: SESSION_USE_ONLY_COOKIES (facultatif)
        - Configuration: SESSION_USE_STRICT_MODE (facultatif)
        - Configuration: SESSION_COOKIE_HTTPONLY (facultatif)
        - Configuration: SESSION_COOKIE_SECURE (facultatif)
        - Configuration: SESSION_COOKIE_SAMESITE (facultatif)
        - Configuration: SESSION_USE_TRANS_ID (facultatif)
        - Configuration: SESSION_CACHE_LIMITER (facultatif)
        - Configuration: SESSION_URL_REWRITER_TAGS (facultatif)
        - Configuration: SESSION_LIFETIME (facultatif)
        - Configuration: SESSION_COOKIE_PATH (facultatif)
        


- **MySQL** : Gestionnaire de données MySQL utilisant PDO.

    Sources: https://github.com/pantaflex44/Syme/blob/v1/components/extended/MySQL.php

    
    Requiert:

        - Composant: 'components\core\Route'

        - Configuration: MYSQL_HOST (impératif)
        - Configuration: MYSQL_PORT (impératif)
        - Configuration: MYSQL_DATABASE (impératif)
        - Configuration: MYSQL_USERNAME (impératif)
        - Configuration: MYSQL_PASSWORD (impératif)
        


- **TwigWrapper** : Permet l'utilisation de ```Twig``` via Syme. Requiert ```twig/twig:^3.0``` via Composer.

    Sources: https://github.com/pantaflex44/Syme/blob/v1/components/extended/TwigWrapper.php

    
    Requiert:

        - Composer: twig/twig:^3.0

        - Composant: 'components\core\Response'
        - Composant: 'components\core\Route'
        


### Middlewares disponibles (/middlewares)

- **CsrfMiddleware** : Permet d'ajouter une protection contre les attaques CSRF aux formulaires HTML.

    Sources: https://github.com/pantaflex44/Syme/blob/v1/middlewares/CsrfMiddleware.php

    
    Requiert:

        - Composant: 'components\core\Request'

        - Composant: 'components\extended\Session'
        - Composant: 'components\extended\TwigWrapper'



<br /><br />
<div style="text-align: center;">

![Syme](./Syme.png)

</div>
<br />