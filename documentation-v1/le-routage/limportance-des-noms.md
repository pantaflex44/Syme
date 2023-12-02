# L'importance des noms

Les règles de routages se doivent d'être correctement nommées.

Nommer ses règles permet de mieux les identifier pour mener, par la suite différentes actions annexes.

Il n'y a pas de convention de nommage précise. Vous êtes responsables de la bonne lecture et compréhension de l'ensemble de vos règles. Toutefois, un point important est à respecter:



{% hint style="info" %}
**Ne jamais définir plusieurs règles avec le même nom**, sous peine de ne voir fonctionner que la première règle créée sous ce dit nom :-)
{% endhint %}

Par exemple:

{% code lineNumbers="true" %}
```php
Route::get('home', '/', function(Response $response) { ... });
Route::post('home', '/', function(Response $response) { ... });
```
{% endcode %}

Seule la première règle `get` sera prise en compte.

Si vous souhaitez regrouper vos règles sous le même nom, rien de plus simple:

{% code lineNumbers="true" %}
```php
Route::map(['GET', 'POST'], 'home', '/', function(Response $response) { ... });
```
{% endcode %}

Sinon, vous pourriez définir une convention de nommage comme suit par exemple:

{% code lineNumbers="true" %}
```php
Route::get('home_get', '/', function(Response $response) { ... });
Route::post('home_post', '/', function(Response $response) { ... });
```
{% endcode %}



#### Rediriger le visiteur en utilisant le nom d'une route

{% code lineNumbers="true" %}
```php
Route::get('article', '/article/{id}', function(int $id, Response $response): Response {
    $response->write("Lecture de l'article $id");
    return $response;
});

Route::get('article_read', '/article/read/{id}', function(int $id, Response $response, Route $route): Response {
    // marquer l'article comme lu
    // puis rediriger vers l'article en question
    if ($route::redirect('article', ['id' => $id]) === false) {
        $response->write("Redirection impossible!");
        return $response;
    }
});
```
{% endcode %}





