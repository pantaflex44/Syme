# Lier une fonction (Callback)

Aucune règle n'est utile sans l'exécution du code nécessaire à la création de la réponse à renvoyer au visiteur.

Cette fonction, "Callback", accepte plusieurs arguments:

* Les attributs de la règle de routage
* Le système de Routage ([Route](../../les-objets/route-le-routeur.md))
* La requête courante ([Request](../../les-objets/request-la-requete.md))
* La réponse ([Response](../../les-objets/response-la-reponse.md))
* Le conteneur de données personnelles ([Data](../../les-objets/data-les-donnees-personnelles.md))

Tous ces arguments sont optionnels!



### Définitions

Par défaut, vous pouvez renseigner le callback directement dans la définition de la route:

{% code lineNumbers="true" %}
```php
Route::get('name', '/', function (Response $response): Response {
    return $response;
});
```
{% endcode %}

Mais pour mieux hiérarchiser votre code, un dossier nommé `controllers` est disponible à la racine de l'application. Ce dossier pourra contenir l'ensemble de vos définitions de routage:

{% code title="\controllers\MonControlleur.php" lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

namespace controllers {

    use components\core\Response;

    class MonControlleur
    {
    
        public static function accueil(Response $response): Response
        {
            $response->write("Ma page d'accueil");
            return $response;
        }
    
    }
    
}
```
{% endcode %}

{% code title="\index.php" lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

use components\core\Response;

Route::get('nom_de_la_route', '/', ['MonController', 'accueil']);

```
{% endcode %}

###

### Attributs

`/article/{id:[0-9]+}/action/{name}`

2 attributs présents:

* **id**: un nombre
* **name**: une donnée de type chaîne de caractères

{% code lineNumbers="true" %}
```php
function(int $id, string $name, Response $response): Response {
}
```
{% endcode %}

Automatiquement, les attributs d'une route sont passés comme arguments au callback!

Chaque attribut est optionnel:

{% code lineNumbers="true" %}
```php
function(int $id, Response $response): Response {
}
```
{% endcode %}

Par ailleurs, ces attributs sont aussi accessibles via l'argument `$attributes`:

{% code lineNumbers="true" %}
```php
function(array $attributes, Response $response): Response {
    $id = $attributes['id'];
    $name = $attributes['name'];
}
```
{% endcode %}



### Arguments

Chaque callback peut percevoir d'autres arguments utilisables dans le code nécessaire à la génération d'une réponse.

{% code lineNumbers="true" fullWidth="false" %}
```php
function (Response $response [, Request $request] [, Route $route] [, Data $data]): Response
```
{% endcode %}

###

### Réponse

L'argument de type `Response` permet de renvoyer au visiteur une réponse appropriée:

{% code lineNumbers="true" %}
```php
$response
    ->write("Mon texte à renvoyer")
    ->withStatus(200);
return $response;
```
{% endcode %}

{% code lineNumbers="true" %}
```php
$response
    ->writeObject(['id' => 123, 'name' => 'delete'])
    ->withStatus(200);
return $response;
```
{% endcode %}

