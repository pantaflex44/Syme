# Syme

*L'autre micro-framework PHP.*

**Syme** est un petit framework PHP aidant à la réalisation d'applications Web. Simple et rapide, ce framework a été conçu pour accélérer le développement tout en restant modulaire et efficace. Applications Web, API Rest, Syme est l'outil incontournable de vos projets.


## Installation

### Automatique

```bash
$ composer create-project pantaflex44/syme
```

### Manuelle

**Téléchargement du projet**
```bash
$ git clone https://github.com/pantaflex44/Syme.git syme
$ cd syme
```

**Installation des dépendances**:
```bash
$ composer install
```

**Edition des configurations**:
```bash
$ sudo nano config.php
```


## Exemple d'utilisation

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



# Documentation (v1)

## Complète

*(en cours...)*
https://pantaflex44.gitbook.io/documentation-de-syme/


## Rapide

### Les composants du Framework (/components/core)

- **Route** : Représente le moteur de routage

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/core/Route.php

    *Schéma*:

    - ```::extendWith(string $class): void``` : Ajoute un composant au système de routage pour le rendre accessible dans chaque routes, middlewares, ou autres composants
    - ```::isAsset(Request $request): string|false``` : Indique si l'url correspond à un fichier à télécharger (fichier dans le dossier '/public')
    - ```::sendAsset(string $filepath): void``` : Envoie au visiteur le fichier correspondant à l'url (fichier dans le dossier '/public')
    - ```::exists(string $routeName): bool``` : Indique si une route existe par son nom
    - ```::match(string $routeName): array|false``` : Retourne les informations d'une route par son nom
    - ```::isLinked(string $uri, array $methods = ['GET', 'POST', 'OPTIONS', 'PUT', 'PATCH', 'DELETE']): bool``` : Indique si une route est bien attachée à un chemin et à une ou plusieurs méthodes HTTP
    - ```::getUri(string $routeName, array $params = []): string|false``` : Retourne le chemin associé à une route et spécifiant les attributs
    - ```::getPath(string $routeName, array $params = []): string|false``` : Retourne le chemin complet associé à une route et spécifiant les attributs
    - ```::getUrl(string $routeName, array $params = []): string|false``` : Retourne l'url complète associée à une route et spécifiant les attributs
    - ```::toRequest(string $routeName, array $params = []): Request|false``` : Transforme une route par son nom, en requète HTTP
    - ```::redirect(string $routeName, array $params = [], int $status = 302): false``` : Redirige le visiteur vers une route en fonction de son nom
    - ```::apply(Request $request): array``` : Applique la logique d'une route en fonction d'une requète passée en paramètres. Renvoie la requète modifiée et la réponse à envoyer
    - ```::sendResponse(Request $initialRequest, null|Response $response): void``` : Envoie le contenu d'une réponse en fonction de la requète HTTP
    - ```::any(string $name, string $uri, callable $callback): void``` : Enregistre une route pour toutes les méthodes HTTP
    - ```::map(array $methods, string $name, string $uri, callable $callback): void``` : Enregistre une route pour les méthodes HTTP spécifiées
    - ```::get(string $name, string $uri, callable $callback): void``` : Enregistre une route pour la méthode HTTP GET
    - ```::post(string $name, string $uri, callable $callback): void``` : Enregistre une route pour la méthode HTTP POST
    - ```::put(string $name, string $uri, callable $callback): void``` : Enregistre une route pour la méthode HTTP PUT
    - ```::patch(string $name, string $uri, callable $callback): void``` : Enregistre une route pour la méthode HTTP PATCH
    - ```::delete(string $name, string $uri, callable $callback): void``` : Enregistre une route pour la méthode HTTP DELETE
    - ```::before(?string $routeName, callable|string $middleware): void``` : Enregistre un middleware devant s'exécuter avant la logique d'une route
    - ```::after(?string $routeName, callable|string $middleware): void``` : Enregistre un middleware devant s'exécuter après la logique d'une route


- **Request** : Représente la requète HTTP courante

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/core/Request.php

    *Schéma*:

    - ```__construct(string $url = null)``` : Constructeur
    - ```::current(): Request``` : Retourne la requète courante
    - ```getPath(): string``` : Retourne le chemin de l'url
    - ```getUri(): string``` : Retourne la portion d'url correspondante au chemin
    - ```getQueryString(): array``` : Retourne la liste des attributs de l'url
    - ```getFragment(): string``` : Retourne le fragment de l'url
    - ```getScheme(): string``` : Retourne le schéma HTTP
    - ```getHost(): string``` : Retourne l'hôte
    - ```getPort(): int``` : Retourne le port utilisé par l'hôte
    - ```getDomain(): string``` : Retourne le domaine complet
    - ```getMethod(): string``` : Retourne la méthode HTTP
    - ```getAcceptedLanguage(): array``` : Retourne la liste des languages acceptés
    - ```getAcceptedEncoding(): array``` : Retourne la liste des encodages acceptés
    - ```getAcceptedTypes(): array``` : Retourne la liste des types de données acceptés
    - ```getUserAgent(): string``` : Retourne l'agent HTTP
    - ```getRemoteAddress(): string``` : Retourne l'adresse IP du visiteur
    - ```getRemotePort(): int``` : Retourne le numéro du port utilisé par le visiteur
    - ```getUrl(bool $full = false): string``` : Retourne l'url correspondante
    - ```hasArgument(string $name): bool``` : Indique si l'url contient des attributs
    - ```getArgument(string $name): false|string``` : Retourne un attribut par son nom
    - ```isXHRRequest(): bool``` : Indique si c'est une requète AJAX
    - ```hasHeader(string $header): bool``` : Indique si une entète HTTP existe
    - ```getHeaders(): array``` : Retourne la liste des entètes HTTP
    - ```getHeader(string $header): false|string``` : Retourne une entète HTTP par son nom
    - ```getAuthorization(): string``` : Retourne le jeton d'authorisation
    - ```getContent(): mixed``` : Retourne le contenu de la requète
    - ```getContentType(): string``` : Retourne le type du contenu de la requète
    - ```getForm(): null|object``` : Retourne le contenu d'un formulaire HTML
    - ```hasForm(): bool``` : Indique si la requète contient un formlaire HTML
    - ```getFiles(): UploadedFiles``` : Retourne la liste des fichiers téléversés
    - ```getReferer(): bool|string``` : Retourne l'url de la page précédente


- **Response** : Représente la réponse HTTP à renvoyer au visiteur

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/core/Response.php

    *Schéma*:

    - ```__construct(string $body = '', string $contentType = 'text/html')``` : Constructeur
    - ```hasHeader(string $header): bool``` : Indique si une entète HTTP a été ajoutée
    - ```removeHeader(string $headerKey): bool``` : Supprime une entète HTTP
    - ```getHeaders(): array``` : Retourne la liste des entètes HTTP
    - ```getHeader(string $header): false|string``` : Retourne une entète HTTP
    - ```withHeader(string $header): Response``` : Ajoute une entète HTTP
    - ```withBearerAuthorization(string $bearer): Response``` : Ajoute un un jeton d'authorization
    - ```withHeaders(array $headers): Response``` : Ajoute une liste d'entètes HTTP
    - ```getStatus(): int``` : Retourne le status HTTP courant
    - ```withStatus(int $status): Response``` : Modifie le status de la réponse HTTP
    - ```getContent(): string``` : Retourne le contenu brut de la réponse HTTP
    - ```getGzipContent(): string``` : Retourne le contenu compressé de la réponse HTTP
    - ```getContentType(): string``` : Retourne le type mime du contenu de la réponse
    - ```getParsed(): array``` : Retourne le contenu décomposé de la réponse HTTP. (XML, Json, multipart, form, data)
    - ```getJson(bool $associative = true): mixed``` : Retourne le contenu au format Json
    - ```clear(): Response``` : Supprime le contenu de la réponse
    - ```write(string $content, string $contentType = 'text/html'): Response``` : Modifie le contenu de la réponse HTTP
    - ```prepend(string $content): Response``` : Ajoute du contenu avant la réponse HTTP
    - ```append(string $content): Response``` : Ajoute du contenu après la réponse HTTP
    - ```writeObject(mixed $object): Response``` : Tranforme un objet PHP en contenu Json puis l'écrit dans la réponse HTTP
    - ```writeJson(string $json): Response``` : Ecrit du contenu Json dans la réponse HTTP


- **Data** : Représente le conteneur de données personnelles capable de traverser l'ensemble des logiques (Routes, Middleswares, Composants)

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/core/Data.php

    *Schéma*:

    - ```__construct()``` : Constructeur.
    - ```count(): int``` : Retourne le nombre de données enregistrées
    - ```clear(): void``` : Supprime toutes les données
    - ```all(): array``` : Retourne toutes les données enregistrées
    - ```exists(string $key): bool``` : Indique si une donnée existe par le nom de sa clef
    - ```get(string $key): mixed``` : Retourne la valeur d'une donnée en fonction du nom de sa clef
    - ```set(string $key, mixed $value): void``` : Modifie la valeur d'une donnée
    - ```delete(string $key): void``` : Supprime une donnée en fonction de sa clef


- **UploadedFile** : Représente la réponse HTTP à renvoyer au visiteur

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/core/UploadedFile.php

    *Schéma*:

    - ```__construct(array $fileinfo)``` : Constructeur
    - ```getName(): string``` : Retourne le nom du fichier
    - ```getType(): string``` : Retourne le type du fichier
    - ```getSize(): int``` : Retourne la taille du fichier
    - ```getReadableSize(): string``` : Retourne la taille du fichier (au format lisible)
    - ```getContentType(): false|string``` : Retourne le type mime du contenu du fichier
    - ```getError(): int``` : Retourne le numéro de l'erreur ou UPLOAD_ERR_OK si aucune erreur
    - ```hasError(): bool``` : Indique si le téléversement comporte une erreur
    - ```moveTo(string $directory): bool``` : Déplace le fichier téléversé dans le dossier de son choix


- **UploadedFiles** : Représente la liste des fichiers téléversés

    *Sources*: https://github.com/pantaflex44/Syme/blob/v1/components/core/UploadedFiles.php

    *Schéma*:

    - ```__construct()``` : Constructeur
    - ```getList(): array``` : Retourne la liste des fichiers téléversés
    - ```hasFile(string $elementName): bool``` : Indique si un fichier est téléversé par le nom du champ de formulaire HTML
    - ```getFile(string $elementName): UploadedFile|array|false``` : Retourne le fichier téléversé par le nom du champ de formulaire HTML
    - ```count(): int``` : Retourne le nombre de fichiers téléversés



### Les extensions facultatives:

#### Composants disponibles (/components/extended)

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
    - ```exists(string $name): bool``` : Retourne si une clef existe par son nom
    - ```get(string $name): mixed``` : Retourne la valeur d'une clef par son nom
    - ```set(string $name, mixed $value): void``` : Défini la valeur d'une clef
    - ```delete(string $name): void``` : Supprime une clef et sa valeur par son nom

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

        


#### Middlewares disponibles (/middlewares)

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

---

### Utiliser Bootstrap

En premier lieu, il faut installer ```Bootstrap``` via *Composer*:

```bash
$ composer require twbs/bootstrap

```


Ensuite, il faut créer le fichier ```bootstrap.install``` nécessaire au déploiement de ```Bootstrap``` à la racine du projet:

```php
<?php

require_once(__DIR__ . '/functions.php');

$bootstrapVendorDir = __DIR__ . '/vendor/twbs/bootstrap/dist';
$bootstrapDir = __DIR__ . '/public/bootstrap';
if (is_dir($bootstrapDir)) removeDir($bootstrapDir);

mkdir($bootstrapDir, 0755);
copyDir($bootstrapVendorDir, $bootstrapDir);

```


Puis, modifier le fichier ```composer.json```:

```json
...
"scripts": {
    "post-update-cmd": [
        "@php bootstrap.install"
    ]
}
...

```


Pour utiliser ```Bootstrap```, vous devez l'inclure dans vos future projet:

```html
<!DOCTYPE html>
<html>
    <head>
        <title>Mon projet</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <script src="./bootstrap/js/bootstrap.min.js"></script>
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        
    </head>
    <body>
        Je suis le projet.
    </body>
</html>

```


**C'est aussi simple que ça ;-)**

Vous pouvez désormais utiliser ```Bootstrap``` dans vos futurs projets.


<br /><br />

---

<br />
<div style="text-align: center;">

![Syme](./Syme.png)

</div>
<br />