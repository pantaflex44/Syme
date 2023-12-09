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

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/extended/Session.php

    *Requiert*:

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

    *Schéma*:

    - ```__construct()``` : Constructeur
    - ```destroy(): void``` : Nettoie et supprime tout le contenu de la session en cours
    - ```exists(string $name): bool``` : Retourne si une clef existe
    - ```get(string $name): mixed``` : Retourne la valeur d'une clef
    - ```set(string $name, mixed $value): void``` : Défini la valeur d'une clef
    - ```delete(string $name): void``` : Supprime une clef et sa valeur

    *Exemple*:

    ```php
    namespace middlewares {

        use components\extended\Session;

        class MyMiddleware {

            public function __invoke(Response $response, Session $session): void {
                if ($session->exists('myKey')) {
                    $value = $session->get('myKey');
                    $response->append(strval($value));
                    $session->delete('myKey');
                }
            }
        }

    }
    ```
        


- **MySQL** : Gestionnaire de données MySQL utilisant PDO.

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/extended/MySQL.php

    *Requiert*:

        - Composant: 'components\core\Route'

        - Configuration: MYSQL_HOST (impératif)
        - Configuration: MYSQL_PORT (impératif)
        - Configuration: MYSQL_DATABASE (impératif)
        - Configuration: MYSQL_USERNAME (impératif)
        - Configuration: MYSQL_PASSWORD (impératif)

    *Schéma*:

    - ```__construct()``` : Constructeur
    - ```__destruct()``` : Destructeur
    - ```getInstance(): \PDO``` : Retourne l'instance PDO
    - ```loadSqlFile(string $sqlFile): void``` : Charge le contenu d'un fichier SQL
    - ```quote(mixed $value): string``` : Assainie une valeur
    - ```execute(string $sql, ?array $args = null): int``` : Execute une requète
    - ```count(string $sql, ?array $args = null): int``` : Retourne le nombre d'enregistrements trouvés
    - ```unique(string $sql, ?array $args = null): array|bool``` : Retourne l'unique enregistrement trouvé
    - ```first(string $sql, ?array $args = null): array|bool``` : Retourne le premier enregistrement trouvé
    - ```all(string $sql, ?array $args = null): array``` : Retourne tous les enregistrements trouvés

    *Exemples*:

    ```php
    // MyMiddleware.php
    namespace middlewares {

        use components\extended\MySQL;

        class MyMiddleware {

            public static function __invoke(MySQL $sql): void {
                $rows = $sql->all('SELECT * FROM users');
                var_dump($rows);
            }
        }

    }
    ```
        


- **TwigWrapper** : Permet l'utilisation de ```Twig``` via Syme. Requiert ```twig/twig:^3.0``` via Composer.

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/extended/TwigWrapper.php

    *Requiert*:

        - Composer: twig/twig:^3.0

        - Composant: 'components\core\Response'
        - Composant: 'components\core\Route'

    *Schéma*:

    - ```__construct(Response $response)``` : Constructeur
    - ```createResponse(string $templateName, array $data = [], bool $toCurrentResponse = true): Response``` : Compile et charge le résultat dans une réponse HTTP
    - ```::addFilter(string $name, callable $callback, array $options = []): void``` : Ajoute un filtre utilisable dans les templates
    - ```::addFunction(string $name, callable $callback, array $options = []): void``` : Ajoute une fonction utilisable dans les templates

    *Exemples*:

    ```php
    // MyMiddleware.php
    namespace middlewares {

        use components\extended\TwigWrapper;

        class MyMiddleware {

            public static function __added(): void {
                TwigWrapper::addFilter('bold', function (string $value): string {
                    return "<b>$value</b>";
                });
            }
        }

    }
    ```

    ```html
    <!-- home.html -->
    {% set foo = 'foo' %}
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>{{ title|upper }}</title>
    </head>
    <body>
        {{ foo|bold|raw }}
    </body>
    </html>
    ```

    ```php
    // index.php
    Route::get('home', '/', function (Response $response, TwigWrapper $twig): Response {
        $twig->createResponse('home.html', [
            'title' => "Syme"
        ]);
        return $response;
    });
    Route::after('home', MyMiddleware::class);
    ```

        


### Middlewares disponibles (/middlewares)

- **CsrfMiddleware** : Permet d'ajouter une protection contre les attaques CSRF aux formulaires HTML.

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/middlewares/CsrfMiddleware.php

    *Requiert*:

        - Composant: 'components\core\Request'

        - Composant: 'components\extended\Session'
        - Composant: 'components\extended\TwigWrapper'

    *Principe*:

    Enregistre une fonction ```Twig``` nommée ```csrf(string $prefix)``` pour ajouter à un formulaire 2 champs cachés permettant de limiter les attaques de types CSRF.
    Pour chaque route ayant ajoutée avant son exécution (Route::before) le middleware ```CsrfMiddleware::class```, une vérification de la présence des 2 champs nécessaires, ainsi
    que la vérification des valeurs attendues, sont effectuées. En cas de manquement, une réponse 403 est renvoyée. En cas de mauvaises valeurs, une réponse 401 est renvoyée.



<br /><br />
<div style="text-align: center;">

![Syme](./Syme.png)

</div>
<br />