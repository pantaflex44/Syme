---
description: Toutes les routes mènent à Rome!
---

# Le routage

## Faisons le point

**Syme** profite d'un système de routage simple et arbitraire.

C'est la pierre angulaire du micro-framework!

Ce système demande un lot de règles précises pour pouvoir mettre en relation L’URL que le visiteur aura demandé, et la réponse que vous souhaitez lui renvoyer.

Pour ce faire, **Syme** s'appuie sur 3 principaux paramètres pour créer une règle:

* Le [nom de la règle](limportance-des-noms.md). Très important, nommer une règle permet de l'identifier tout au long du code.
* La ou les méthodes HTTP qui seront surveillées par le système de routage.
* Et bien évidement, le chemin, partie de l'URL qui identifiera la [requête](../../les-objets/request-la-requete.md) reçue.

Tout ces éléments permettent de fabriquer une règle de routage. Toutefois il manque un dernier élément, et pas des moindre: [la réponse](../../les-objets/response-la-reponse.md)!

La réponse est le code qui sera exécuté lorsque le système de routage aura détecté la bonne route à suivre. Ce code devra renvoyer une réponse au visiteur. Ce code, s'appelle aussi, "_Callback_".



## Un exemple parlant

{% code lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

require_once './core.php';


use components\core\Response;
use components\core\Route;

// Nom: home
// Uri: /
// Méthode: GET
Route::get('home', '/', function (Response $response): Response {
    $response->write("Ceci est ma réponse");
    return $response;
});

```
{% endcode %}

##
