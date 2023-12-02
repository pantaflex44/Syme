# Présentations

<figure><img src="../.gitbook/assets/WebSimply.png" alt=""><figcaption></figcaption></figure>

**Syme** est un micro-framework écrit avec le language PHP. Son but est de permettre de créer des applications web ou des APIs REST, facilement et donc rapidement.

Sa simplicité commence par son architecture et se termine par l'efficacité de votre développement.



## Fonctionnement

Pour faire fonctionner **Syme** vous devez posséder un [serveur Web](serveurs-et-deploiements.md), Apache ou Nginx. C'est le point de départ de votre projet.

**Syme** est l'aiguilleur qui va mettre en relation les URLs et les pages que vous avez créer. Pour cela, le framework intercepte les URLs, les analyse, et agit en fonction des règles que vous avez définies.

Chaque règle permet d'exécuter le code que vous avez créé pour retourner une réponse HTTP à renvoyer aux navigateurs.

Un exemple d'application:

{% code title="./controllers/ArticleController.php" lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

use components\core\Response;

class ArticleController {

    public static function getFromId(int $id, Response $response): Response
    {
        $response
            ->writeObject(['article Id' => $id])
            ->withStatus(302);

        return $response;
    }

}
```
{% endcode %}

{% code title="./index.php" lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

require_once './core.php';
require_once './controllers/ArticleController.php';


use components\core\Response;
use components\core\Route;

// URL: /
Route::get('home', '/', function (Response $response): Response {
    $response
        ->write("Bonjour le monde");

    return $response;
});

// URL: /article/125
Route::get('article', '/article/{id:[0-9]+}', ['ArticleController', 'getFromId']);

```
{% endcode %}

&#x20;

### L'utilisateur demande, **Syme** répond

Le principe est simple. L'utilisateur invoque via une URL, **Syme** analyse cette demande, puis aiguille pour renvoyer la bonne réponse.&#x20;

Pour cela, **Syme** utilise les 2 composants primitifs: [Request](../les-objets/request-la-requete.md) et [Response](../les-objets/response-la-reponse.md).

Brièvement, la requête HTTP comprend les détails de la demande de l'utilisateur, et la réponse HTTP, le résultat du traitement effectué par la règle de routage définie dans le code.



### La modularité

**Syme** vous permet, non seulement, de créer vos [Controlleurs](le-routage/creer-une-regle-de-routage.md) mais aussi des [Middlewares](le-routage/middlewares/).&#x20;

Pour aller encore plus loin, **Syme** à la capacité d'étendre ses composants en vous proposant de [créer les vôtres](../aller-plus-loin/#creer-ses-composants), mais surtout de pouvoir, automatiquement, les injecter dans tous les Middlewares et Controlleurs de votre application!



## Auteurs et Remerciements

Développement principal:

* Christophe LEMOINE (pantaflex at tuta io)

Documentation créée et maintenue par:

* Christophe LEMOINE (pantaflex at tuta io)



## Licences

WebSimply Micro-Framework © 2022-2024 is licensed under [MIT](https://opensource.org/license/mit/)[ License](https://opensource.org/license/mit/).&#x20;

WebSimply Documentation © 2022-2024 is licensed under [CC BY-NC-ND 4.0 <img src="https://chooser-beta.creativecommons.org/img/cc-logo.f0ab4ebe.svg" alt="" data-size="line"><img src="https://chooser-beta.creativecommons.org/img/cc-by.21b728bb.svg" alt="" data-size="line">](http://creativecommons.org/licenses/by-nc-nd/4.0/?ref=chooser-v1)



