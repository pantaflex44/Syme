---
description: L'autre micro-framework PHP.
layout:
  title:
    visible: true
  description:
    visible: true
  tableOfContents:
    visible: true
  outline:
    visible: true
  pagination:
    visible: true
---

# Syme

**Syme** est un petit framework PHP aidant à la réalisation d'applications Web. Simple et rapide, ce framework a été conçu pour accélérer le développement tout en restant modulaire et efficace. Applications Web, API Rest, **Syme** est l'outil incontournable de vos projets.

<pre class="language-php" data-line-numbers><code class="lang-php">&#x3C;?php

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

<strong>Route::get('article', '/article/{id:[0-9]+}/author/{name}', function (int $id, string $name, Response $response, Data $data): Response {
</strong>    $obj = ['article id' => $id, 'author name' => $name];
    $obj = array_merge($obj, $data->all());

    $response
        ->writeObject($obj)
        ->withStatus(200);

    return $response;
});
Route::before('article', function(Request $request, Response $response, Data $data): void {
    $data->set('who am i ?', "I'm a midlleware ;-)");
});
</code></pre>



***



### Fonctionnalités

<table data-view="cards"><thead><tr><th></th><th></th><th></th></tr></thead><tbody><tr><td><mark style="color:orange;"><strong>Routeur HTTP</strong></mark></td><td>Analyse des requêtes HTTP à la volée, corrélation automatique entre les urls et une fonction PHP en respectant la méthode HTTP utilisée, inspection des urls en respectant des patterns regex, injection des attributs du pattern directement en paramètres de la fonction associée, etc.</td><td></td></tr><tr><td><mark style="color:orange;"><strong>Middlewares</strong></mark></td><td>Amélioration du traitement des requêtes, en la modifiant, ou en modifiant la future réponse ou en traitant les données personnelles.</td><td></td></tr><tr><td><mark style="color:orange;"><strong>Formulaires HTML</strong></mark></td><td>Accéder facilement aux données des formulaires envoyés ainsi qu'aux fichiers transmis.</td><td></td></tr></tbody></table>

<figure><img src=".gitbook/assets/WebSimply.png" alt="WebSimply"><figcaption></figcaption></figure>

