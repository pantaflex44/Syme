# Créer un middleware

Un "Middleware" est une fonction prenant pour paramètres, les arguments de types suivants:

* Request: la requête courante
* Response: la future réponse
* Data: le conteneur de données personnelles

Les attributs de la règle de routage correspondante sont aussi accessible via l'argument `$attributes` de type `array`



### Les middleware "tout en un"

{% code title="/index.php" lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

require_once './core.php';


use components\core\Data;
use components\core\Request;
use components\core\Response;


Route::get('home', '/{name}', function (Response $response, Data $data): Response {
    $response->write("je suis le traitement de la route<br />");
    return $response;
});

Route::before('home', function(Response $response): void {
    $response->prepend("je suis le middleware avant le traitement de la route<br />");
});

Route::after('home', function(Response $response, array $attributes): void {
    $response->append("je suis le middleware après le traitement de la route<br />");
    $response->append(json_encode($attributes));
});

```
{% endcode %}



### Ranger ses middlewares

{% code title="\middlewares\MyMiddleware.php" lineNumbers="true" %}
```php
<?php

namespace middlewares {

    use components\core\Data;
    use components\core\Response;


    class MyMiddleware
    {

        public function __invoke(array $attributes, Data $data): void
        {
            $data->set('route attributes', $attributes);
        }

    }

}
```
{% endcode %}

{% code title="\index.php" lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

require_once './core.php';


use components\core\Data;
use components\core\Request;
use components\core\Response;
use middlewares\MyMiddleware;


Route::get('home', '/{name}', function (Response $response, Data $data): Response {
    $response->writeObject($data->all());
    return $response;
});

Route::before('home', MyMiddleware::class);
```
{% endcode %}



### Lier un middleware

Il existe 2 méthodes pour lier un middleware au traitement d'une règle de routage.

* Lier directement son middleware à une règle de routage
* Lier le middleware à l'ensemble des règles de routage

L'objet [Route](../../../les-objets/route-le-routeur.md) contient 2 méthodes:

* `Route::before`
* `Route::after`

Ces 2 méthodes prennent pour arguments le nom de la route associée et un callback possédant à son tour les arguments optionnels, [Request](../../../les-objets/request-la-requete.md), [Response](../../../les-objets/response-la-reponse.md), [Data](../../../les-objets/data-les-donnees-personnelles.md), mais aussi la liste des attributs de la règle de routage courante via l'argument `$attributes` de type `array.`

{% hint style="info" %}
Le nom de la route associée peut être remplacé par la valeur `null` pour associer le middleware à l'ensemble des routes définies!
{% endhint %}

