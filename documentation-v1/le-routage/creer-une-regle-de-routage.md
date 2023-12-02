# Créer une règle de routage

Comme vu dans la présentation du système de routage, des règles précises doivent être créées pour que **Syme** fonctionne correctement.



## Au commencement, les méthodes HTTP...

Une requête HTTP est conçue sur la base de méthodes. Les méthodes connues et pratiquées par **Syme** sont:

* GET
* POST
* PUT
* DELETE
* PATCH
* OPTIONS

La méthode courante peut se retrouver via l'objet [Request](../../les-objets/request-la-requete.md) accessible depuis les callback, [middlewares](middlewares/) et [composants personnalisés](../../aller-plus-loin/#creer-ses-composants).



#### GET

La méthode GET correspond à la méthode par défaut, par exemple, quand le visiteur entre une URL dans la barre de son navigateur.

```php
Route::get("nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```



#### POST

La méthode POST est la méthode généralement employée lors de l'envoie de données par exemple.

```php
Route::post("nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```



#### PUT

La méthode PUT est généralement employée pour créer ou modifier de données.

```php
Route::put("nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```



#### PATCH

La méthode PATCH est utilisée pour signaler la modification de données.

```php
Route::patch("nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```



#### DELETE

La méthode DELETE, très souvent utilisée, permet de signaler au système de routage, qu'une donnée doit être supprimée.

```php
Route::delete("nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```



### Les règles spéciales

#### any

**Syme** met à votre disposition la possibilité d'écouter toutes les méthodes connues pour un chemin particulier. Pour ce faire, la fonction `any` sera utilisée:

```php
Route::any("nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```



#### map

Bien évidement, vous pouvez aussi dire au système de routage, de surveiller une liste de méthodes pour un chemin donné. Par exemple, nous allons écouter la méthode GET et POST pour un chemin qui s'occupera de renvoyer puis traiter un formulaire:

```php
Route::map(['GET', 'POST'], "nom de la règle", "/chemin/a/surveiller", {fonction à exécuter});
```

_Exemple concret_:

{% code lineNumbers="true" %}
```php
<?php

declare(strict_types=1);

require_once './core.php';


use components\core\Request;
use components\core\Response;
use components\core\Route;


Route::map(['GET', 'POST'], 'form_test', '/', function (Request $request, Response $response): Response {
    if ($request->getMethod() === 'POST' && $request->hasForm()) {
        $response->writeObject($request->getForm());

    } else {
        $response->write('
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>title</title>
  </head>
  <body>
    <form method="post">
        <input type="text" name="input1[]" value="" />
        <input type="text" name="input1[]" value="" />
        <input type="email" name="input2" value="" />
        <input type="range" name="range" min="0" max="100" value="50" />
        <input type="date" name="date" value="" />
        <input type="submit" name="submit" value="Envoyer" />
    </form>
  </body>
</html>
        ');

    }

    return $response;
});
```
{% endcode %}



