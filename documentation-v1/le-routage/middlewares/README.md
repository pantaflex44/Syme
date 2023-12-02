# Middlewares

Un "Middleware" est une fonction permettant d'agir sur la requête courante, la future réponse, tout en ayant accès au conteneur de données personnelles, et aux attributs définis par la règle de routage associée.

Ces fonctions peuvent être exécutées avant et/ou après le traitement de la route. C'est le but même des Middleware.



{% code lineNumbers="true" %}
```php
Route::get('my_route', '/{token}', function (Response $response, Data $data): Response {
    $response
        ->writeObject($data->all())
        ->withStatus(200);

    return $response;
});

Route::before('my_route', MyMiddleware::class);

Route::before('my_route', function (Request $request, Response $response, Data $data): void {
    $data->set('mid1', 'je suis le middleware 1, avant le traitement de la route');
});
Route::before('my_route', function (array $attributes, Request $request, Response $response, Data $data): void {
    $data->set('mid2', 'je suis le middleware 2, avant le traitement de la route');
    $data->set('attributes', $attributes);
});

Route::after('my_route', function (Request $request, Response $response, Data $data): void {
    $data->set('mid3', 'je suis le middleware 3, après le traitement de la route');
});
Route::after('my_route', function (Request $request, Response $response, Data $data): void {
    $data->set('mid4', 'je suis le middleware 4, après le traitement de la route');
});

```
{% endcode %}

