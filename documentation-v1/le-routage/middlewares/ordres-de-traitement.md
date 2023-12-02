# Ordres de traitement

Un middleware interagi avec les règles de routage pour modifier la requête courante et la future réponse à envoyer.

Cette interaction peut se produire, avant le traitement de la route courante, ou après.

C'est le but même d'un middleware.

* `Route::before` : exécute le middleware avant le traitement de la route
* `Route::after` : exécute le middleware après le traitement de la route



