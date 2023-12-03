<?php

/**
 * Copyright 2023-2024 Christophe LEMOINE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the “Software”),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);


require_once './core.php';


use components\core\Data;
use components\core\Request;
use components\core\Response;
use components\core\Route;
use components\extends\MyComponent;
use components\extends\TwigWrapper;
use middlewares\CsrfMiddleware;


Route::get('home_get', '/', function (Response $response): Response {
    $response
        ->write('
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
        <input type="range" name="range1" min="0" max="100" value="50" />
        <input type="date" name="date" value="" />
        <input type="submit" name="submit" value="ok" />
    </form>
  </body>
</html>
        ');

    return $response;
});
Route::post('home_post', '/', function (Request $request, Response $response, Route $route): null|Response {
    if ($route::redirect('home_get') === false) {
        $response
            ->writeObject($request->getForm());

        return $response;
    }

    return null;
});

Route::get('bob2', '/bob/{token}', function (string $token, Response $response): Response {
    $response
        ->writeObject($token)
        ->withStatus(200);

    return $response;
});

Route::get('bob', '/bob/{id:[0-9]+}/super/{name}', function (int $id, string $name, Response $response, Data $data, MyComponent $mc): Response {
    $obj = ['id' => $id, 'name' => $name];
    $obj = array_merge($obj, $data->all());

    $response
        ->writeObject($obj)
        ->withStatus(200);

    return $response;
});
Route::before('bob', CsrfMiddleware::class);
Route::before('bob', function (Request $request, Response $response, Data $data): void {
    $data->set('valeur de la donnée bob', $data->get('bob'));
});

Route::map(['GET', 'POST'], 'form', '/form', function (Request $request, Response $response, TwigWrapper $twig): Response {
    if ($request->getMethod() === 'POST' && $request->hasForm()) {
        $response->writeObject($request->getForm());

    } else {
        // modifie automatiquement le contenu de la réponse courante
        $twig->renderToResponse('form.html', [
            'title' => "Syme"
        ]);
    }

    return $response;
});


Route::get('home', '/{name}', function (Response $response, Data $data): Response {
    $response->write("je suis le traitement de la route<br />");
    return $response;
});
Route::before('home', function (Response $response): void {
    $response->prepend("je suis le middleware avant le traitement de la route<br />");
});
Route::after(null, function (array $attributes, Response $response): void {
    $response->append("je suis le middleware après le traitement de la route<br />");
    $response->append(json_encode($attributes));
});



