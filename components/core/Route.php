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

namespace components\core {

    use JetBrains\PhpStorm\NoReturn;

    class Route {

        /**
         * @var array $routes Liste des routes enregistrées
         */
        protected static array $routes = [];

        /**
         * @var array|array[] $middlewares Liste des middlewares applicables
         */
        protected static array $middlewares = ['before' => [], 'after' => []];

        /**
         * @var array $components Liste des composants personnalisés accessibles dans les callbacks et middlewares
         */
        protected static array $components = [];

        /** Enregistre une nouvelle règle
         * @param string $name Nom de la route
         * @param array $methods Méthode attendue (GET, POST, PUT, PATCH, DELETE)
         * @param string $uri Chemin de la route
         * @param callable $callback Function à appeler en cas de détection de la route
         * @return bool true, la nouvelle règle est enregistrée, sinon, false
         */
        private static function store(string $name, array $methods, string $uri, callable $callback): bool {
            $name = strtolower(trim($name));
            if (self::exists($name))
                return false;

            $methods = array_filter(
                    array_map(
                            fn($m) => strtoupper(trim($m)),
                            $methods
                    ),
                    fn($m) => $m === 'GET' || $m === 'POST' || $m === 'PUT' || $m === 'PATCH' || $m === 'DELETE'
            );
            if (count($methods) === 0)
                return false;

            $uri = trim($uri);
            if (!str_starts_with($uri, '/'))
                $uri = '/' . $uri;
            if (str_ends_with($uri, '/'))
                $uri = substr($uri, 0, strlen($uri) - 1);
            if (self::isLinked($uri, $methods))
                return false;

            if (!is_callable($callback))
                return false;

            $route = [];
            $params = [];
            $parsed = explode('/', $uri);
            foreach ($parsed as $node) {
                if (trim($node) === '')
                    continue;

                if (str_starts_with($node, '{') && str_ends_with($node, '}')) {
                    $node = trim(substr($node, 1, strlen($node) - 2));
                    $rule = explode(':', $node);
                    if (count($rule) > 0) {
                        $paramName = trim($rule[0]);
                        $regexp = '';
                        if (count($rule) > 1)
                            $regexp = trim($rule[1]);
                        if (strlen($regexp) === 0)
                            $regexp = '[^\/.]+';
                        $node = '(?<' . preg_quote($paramName) . '>' . $regexp . ')';
                        $params[] = $paramName;
                    }
                } else {
                    $node = preg_quote($node);
                }

                $route[] = $node;
            }
            $route = implode('/', $route);
            if (!str_starts_with($route, '/'))
                $route = '/' . $route;
            $route = '#^' . $route . '$#i';

            self::$routes[$name] = [
                'methods' => $methods,
                'uri' => $uri,
                'callback' => $callback,
                'route' => $route,
                'params' => $params,
            ];

            return true;
        }

        /** Ajoute un middleware à une route
         * @param string $order
         * @param string|null $routeName Nom de la route concernée, ou, null pour toutes les routes
         * @param callable|string $middleware Middleware à ajouter
         * @return void
         */
        private static function add(string $order, ?string $routeName, callable|string $middleware): void {
            if (is_null($routeName))
                $routeName = '_';

            if ($routeName !== '_' && !self::exists($routeName))
                return;
            if (!isset(self::$middlewares[$order][$routeName]))
                self::$middlewares[$order][$routeName] = [];

            self::$middlewares[$order][$routeName][] = $middleware;
        }

        /** Applique les middlewares
         * @param string $order Ordre d'application
         * @param string $routeName Nom de ka route concernée
         * @param Request $request Requète courante
         * @param Response $response Réponse courante
         * @param Data $data Données personnelles
         * @return void
         * @throws \ReflectionException
         */
        private static function applyMiddleware(string $order, string $routeName, Request &$request, Response &$response, Data &$data, array $namedParameters = []): void {
            $middlewares = array_merge(
                    self::$middlewares[$order][$routeName] ?? [],
                    self::$middlewares[$order]['_'] ?? []
            );

            foreach ($middlewares as $middleware) {
                $injectables = [
                    Route::class => new self(),
                    Request::class => $request,
                    Response::class => $response,
                    Data::class => $data
                ];
                if (is_callable($middleware)) {
                    $method = new \ReflectionFunction($middleware);
                    $params = self::paramsInjection($method, $injectables, $namedParameters);
                    $method->invokeArgs($params);
                } elseif (gettype($middleware) === 'string') {
                    $class = new \ReflectionClass($middleware);
                    if ($class->hasMethod('__invoke')) {
                        $method = $class->getMethod('__invoke');
                        $params = self::paramsInjection($method, $injectables, $namedParameters);
                        $method->invokeArgs(new $middleware, $params);
                    }
                }
            }
        }

        /** Applique les middlewares éxécutée avant une route
         * @param string $routeName Nom de ka route concernée
         * @param Request $request Requète courante
         * @param Response $response Réponse courante
         * @param Data $data Données personnelles
         * @return void
         */
        private static function applyBeforeMiddleware(string $routeName, Request &$request, Response &$response, Data &$data, array $namedParameters = []): void {
            self::applyMiddleware('before', $routeName, $request, $response, $data, $namedParameters);
        }

        /** Applique les middlewares éxécutée après une route
         * @param string $routeName Nom de ka route concernée
         * @param Request $request Requète courante
         * @param Response $response Réponse courante
         * @param Data $data Données personnelles
         * @return void
         * @throws \ReflectionException
         */
        private static function applyAfterMiddleware(string $routeName, Request &$request, Response &$response, Data &$data, array $namedParameters = []): void {
            self::applyMiddleware('after', $routeName, $request, $response, $data, $namedParameters);
        }

        /** Permet l'injection de paramètres
         * @param \ReflectionFunction|\ReflectionMethod|null $method Méthode ou fonction
         * @param array $typeDependencies Liste des dépendances par types [Object::class => instance]
         * @param array $nameDependencies Liste des dépendances par nom ['id' => value]
         * @return array Tableau associatif des dépendances injectées
         * @throws \ReflectionException
         */
        private static function paramsInjection(\ReflectionFunction|\ReflectionMethod|null $method, array $typeDependencies = [], array $nameDependencies = []): array {
            if (is_null($method))
                return [];

            $components = array_keys(self::$components);
            $params = [];
            $hasAttributesParameter = false;
            foreach ($method->getParameters() as $param) {
                $type = $param->getType()->getName();
                $name = $param->getName();

                if ($hasAttributesParameter === false && $name === 'attributes' && $type === 'array') {
                    $hasAttributesParameter = true;
                } elseif (isset($typeDependencies[$type])) {
                    $params[$name] = $typeDependencies[$type];
                } elseif (in_array($type, $components)) {
                    if (is_null(self::$components[$type])) {
                        $class = new \ReflectionClass($type);
                        $ctor = $class->getConstructor();
                        $ctorParams = self::paramsInjection($ctor, $typeDependencies, $nameDependencies);
                        self::$components[$type] = $class->newInstanceArgs($ctorParams);
                    }
                    $params[$name] = self::$components[$type];
                } elseif (isset($nameDependencies[$name])) {
                    $params[$name] = $nameDependencies[$name];
                }
            }

            if ($hasAttributesParameter)
                $params['attributes'] = $nameDependencies;

            return $params;
        }

        /** Etend l'injection de paramètres
         * @param string $class Nom complet de la classe à ajouter
         * @return void
         */
        public static function extendWith(string $class): void {
            if (trim($class) !== '' && !isset(self::$components[$class]))
                self::$components[$class] = null;
        }

        /** Si une requète correspond à la demande d'un asset, on renvoie le chemin du fichier
         * @param Request $request Requète correspondante
         * @return false false, si l'asset n'éxiste pas, sinon, le chemin du fichier
         */
        public static function isAsset(Request $request): string|false {
            $uri = $request->getUri();
            $filepath = ASSETS_PATH . $uri;
            return is_file($filepath) && is_readable($filepath) ? $filepath : false;
        }

        /** Envoie d'un fichier
         * @param string $filepath Chemin du fichier à envoyer
         * @return void
         */
        #[NoReturn] public static function sendAsset(string $filepath): void {
            $mimetype = mime_content_type($filepath);
            $size = filesize($filepath);
            $time = date('r', filemtime($filepath));
            $range = $_SERVER['HTTP_RANGE'] ?? null;

            $fm = @fopen($filepath, 'rb');
            if (!$fm) {
                header('HTTP/1.1 500 Internal server error');
                exit();
            }

            $begin = 0;
            $end = $size - 1;
            if (!is_null($range)) {
                if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $range, $matches)) {
                    $begin = intval($matches[1]);
                    if (!empty($matches[2]))
                        $end = intval($matches[2]);
                }
                header('HTTP/1.1 206 Partial Content');
            } else {
                header('HTTP/1.1 200 OK');
            }

            header("Content-Type: $mimetype");
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Accept-Ranges: bytes');
            header('Content-Length:' . (($end - $begin) + 1));
            if (!is_null($range))
                header("Content-Range: bytes $begin-$end/$size");
            header('Content-Disposition: inline; filename=' . basename($filepath));
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: $time");

            $cur = $begin;
            fseek($fm, $begin, 0);

            while (!feof($fm) && $cur <= $end && (connection_status() == 0)) {
                print fread($fm, min(ASSETS_PACKET_SIZE * 16, ($end - $cur) + 1));
                $cur += ASSETS_PACKET_SIZE * 16;
            }

            @fclose($fm);
            exit();
        }

        /** Test si une route existe par son nom
         * @param string $routeName Nom de la route
         * @return bool true, la route existe, sinon, false
         */
        public static function exists(string $routeName): bool {
            $routeName = strtolower(trim($routeName));

            return in_array($routeName, array_keys(self::$routes));
        }

        /** Récupère les détails d'une règle en fonction du nom de la route
         * @param string $routeName Nom de la route
         * @return array|false Détails de la règle, ou false si la route n'éxiste pas
         */
        public static function match(string $routeName): array|false {
            $routeName = strtolower(trim($routeName));

            foreach (self::$routes as $name => $details) {
                if ($name === $routeName)
                    return [
                        'methods' => $details['methods'],
                        'uri' => $details['uri'],
                        'callback' => $details['callback']
                    ];
            }

            return false;
        }

        /** Test si un chemin est bien attaché à une route
         * @param string $uri Chemin à tester
         * @return bool true, le chemin est lié, sinon, false
         */
        public static function isLinked(string $uri, array $methods = ['GET', 'POST', 'OPTIONS', 'PUT', 'PATCH', 'DELETE']): bool {
            $uri = trim($uri);
            if (!str_starts_with($uri, '/'))
                $uri = '/' . $uri;
            if (str_ends_with($uri, '/'))
                $uri = substr($uri, 0, strlen($uri) - 1);

            foreach (self::$routes as $name => $details) {
                if (strtolower($details['uri']) === strtolower($uri)) {
                    foreach ($methods as $method) {
                        if (in_array($method, $details['methods']))
                            return true;
                    }
                }
            }

            return false;
        }

        /** Retourne le chemin d'une route par son nom, en appliquant les attributs passés en paramètres
         *
         * eg: /my/route
         *
         * @param string $routeName Nom de la route
         * @param array $params Attributs de la route
         * @return string|false Chemin préparé de la route, ou false en cas d'erreur
         */
        public static function getUri(string $routeName, array $params = []): string|false {
            $found = null;
            $routeName = strtolower(trim($routeName));

            foreach (self::$routes as $name => $details) {
                if ($name === $routeName) {
                    $found = $details;
                    break;
                }
            }

            if (is_null($found))
                return false;

            $filteredParams = [];
            foreach ($params as $key => $value) {
                if (in_array($key, $found['params']))
                    $filteredParams[$key] = $value;
            }

            $uri = [];
            $parsed = explode('/', $found['uri']);
            foreach ($parsed as $node) {
                if (trim($node) === '')
                    continue;

                if (str_starts_with($node, '{') && str_ends_with($node, '}')) {
                    $node = trim(substr($node, 1, strlen($node) - 2));
                    $rule = explode(':', $node);
                    if (count($rule) > 0) {
                        $paramName = trim($rule[0]);
                        if (in_array($paramName, array_keys($filteredParams))) {
                            $node = $filteredParams[$paramName];
                        } else {
                            $node = $paramName;
                        }
                    }
                }

                $uri[] = $node;
            }
            $uri = implode('/', $uri);
            if (!str_starts_with($uri, '/'))
                $uri = '/' . $uri;

            return $uri;
        }

        /** Retourne le chemin complet d'une route par son nom, en appliquant les attributs passés en paramètres
         *
         * eg: /document_root/my/route
         *
         * @param string $routeName Nom de la route
         * @param array $params Attributs de la route
         * @return string|false Chemin complet et préparé de la route, ou false en cas d'erreur
         */
        public static function getPath(string $routeName, array $params = []): string|false {
            $uri = self::getUri($routeName, $params);
            if ($uri === false)
                return false;

            $root = trim(dirname($_SERVER['SCRIPT_NAME']));
            if (str_ends_with($root, '/'))
                $root = substr($root, 0, strlen($root) - 1);
            if (!str_starts_with($root, '/'))
                $root = '/' . $root;

            return $root . $uri;
        }

        /** Retourne l'url complète d'une route par son nom, en appliquant les attributs passés en paramètres
         *
         * eg: https://host/document_root/my/route
         *
         * @param string $routeName Nom de la route
         * @param array $params Attributs de la route
         * @return string|false Url complète et préparée de la route, ou false en cas d'erreur
         */
        public static function getUrl(string $routeName, array $params = []): string|false {
            $path = self::getPath($routeName, $params);
            if ($path === false)
                return false;

            $host = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";

            return $host . $path;
        }

        /** Retourne un objet Request depuis le nom d'une route, en appliquant les attributs passés en paramètres
         * @param string $routeName Nom de la route
         * @param array $params Attributs de la route
         * @return Request|false Requète, ou, false en cas d'erreur
         */
        public static function toRequest(string $routeName, array $params = []): Request|false {
            $url = self::getUrl($routeName, $params);
            if ($url === false)
                return false;

            return new Request($url);
        }

        /** Redirige vers la route nommée, en appliquant les attributs passés en paramètres
         * @param string $routeName Nom de la route
         * @param array $params Attributs de la route
         * @return false false en cas d'erreur
         */
        public static function redirect(string $routeName, array $params = [], int $status = 302): false {
            $url = self::getUrl($routeName, $params);
            if ($url === false)
                return false;

            header("Location: $url", true, $status);
            exit();
        }

        /** Applique la logique d'une route en fonction de la requète HTTP passée en paramètre
         * @param Request $request Requète à analyser
         * @return array Response déduite à envoyer
         * @throws \ReflectionException
         */
        public static function apply(Request $request): array {
            $found = false;
            $called = false;

            $data = new Data();
            $response = new Response();

            foreach (self::$routes as $name => $details) {
                if (!in_array($request->getMethod(), $details['methods']))
                    continue;

                if (preg_match($details['route'], $request->getUri(), $matches, PREG_UNMATCHED_AS_NULL)) {
                    $found = true;

                    if (is_callable($details['callback'])) {
                        $callbackParams = [];
                        foreach ($details['params'] as $param) {
                            if (isset($matches[$param])) {
                                $value = trim($matches[$param]);
                                $callbackParams[$param] = parse_value($value, true);
                            }
                        }

                        self::applyBeforeMiddleware($name, $request, $response, $data, $callbackParams);

                        $func = new \ReflectionFunction($details['callback']);
                        $params = self::paramsInjection($func, [
                                    Route::class => new self(),
                                    Request::class => $request,
                                    Response::class => $response,
                                    Data::class => $data
                                        ], $callbackParams);
                        $response = $func->invokeArgs($params);

                        self::applyAfterMiddleware($name, $request, $response, $data, $callbackParams);

                        $called = true;
                    }

                    break;
                }
            }

            if (!$found) {
                header('HTTP/1.1 404 Not Found');
                exit();
            } elseif (!$called) {
                header('HTTP/1.1 501 Not Implemented');
                exit();
            } elseif (is_null($response)) {
                header('HTTP/1.1 400 Bad Request');
                exit();
            }

            return [$request, $response];
        }

        /** Envoie le contenu d'une réponse
         * @param Request $initialRequest Requète initiale ayant aménée à la réponse
         * @param Response $response Réponse déduite de la requète
         * @return void
         */
        #[NoReturn] public static function sendResponse(Request $initialRequest, null|Response $response): void {
            if (is_null($response)) {
                header('HTTP/1.1 500 Internal server error');
                exit();
            }

            $gzipAccepted = USE_COMPRESSION && in_array('gzip', $initialRequest->getAcceptedEncoding()) && strlen($response->getContent()) >= 2048;
            $content = $gzipAccepted ? $response->getGzipContent() : $response->getContent();
            $length = strlen($content);

            $etag = md5($content);
            header('Vary: If-None-Match');
            if ($gzipAccepted)
                header('Content-Encoding: gzip');

            $if_none_match = ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag;
            $if_modified_since = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? false;
            if (USE_CACHE && $if_none_match && ($if_modified_since === false || ($if_modified_since && (strtotime($if_modified_since) + CACHE_DELAY) > time()))) {
                header('HTTP/1.1 304 Not Modified');
                exit();
            }

            foreach ($response->getHeaders() as $header)
                header($header, false);

            $lastModified = gmdate('D, d M Y H:i:s ', time()) . ' GMT';
            $expires = gmdate('D, d M Y H:i:s ', time() + CACHE_DELAY) . ' GMT';
            header('Etag: ' . $etag, true);
            header('Last-Modified: ' . $lastModified, true);
            header('Expires: ' . $expires, true);
            header('Pragma: cache', true);
            header('Cache-Control: max-age=' . CACHE_DELAY, true);
            header('Content-Type: ' . $response->getContentType() . '; charset: UTF-8', true);
            header('Content-Length: ' . $length, true);

            http_response_code($response->getStatus());
            print $content;

            exit();
        }

        /** Créé une règle pour toutes les méthodes HTTP, en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         *
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function any(string $name, string $uri, callable $callback): void {
            self::store($name, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $callback);
        }

        /** Créé une règle pour l'ensemble des méthodes HTTP désirées, en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         * @param array $methods Liste des métodes sous forme d'un tableau. eg: ['GET', 'POST', 'PUT']
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function map(array $methods, string $name, string $uri, callable $callback): void {
            self::store($name, $methods, $uri, $callback);
        }

        /** Créé une règle pour la méthode HTTP 'GET', en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         *
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function get(string $name, string $uri, callable $callback): void {
            self::store($name, ['GET'], $uri, $callback);
        }

        /** Créé une règle pour la méthode HTTP 'POST', en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         *
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function post(string $name, string $uri, callable $callback): void {
            self::store($name, ['POST'], $uri, $callback);
        }

        /** Créé une règle pour la méthode HTTP 'PUT', en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         *
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function put(string $name, string $uri, callable $callback): void {
            self::store($name, ['PUT'], $uri, $callback);
        }

        /** Créé une règle pour la méthode HTTP 'PATCH', en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         *
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function patch(string $name, string $uri, callable $callback): void {
            self::store($name, ['PATCH'], $uri, $callback);
        }

        /** Créé une règle pour la méthode HTTP 'DELETE', en spécifiant un nom pour la route,
         * le chmin de la route (sans le chemin de la racine de l'application), et en spéciafiant la
         * fonction qui sera appelée en cas de détection.
         *
         * EG: https://localhost/WebSimply/bob/125/super/bob => /bob/125/super/bob , où /Syme est le dossier sur
         * le serveur, contenant l'application.
         *
         * @param string $name Nom unique de la route
         * @param string $uri Chemin de la route sans le chemin du document
         * @param callable $callback Fonction à appeler en cas de détection
         * @return void
         */
        public static function delete(string $name, string $uri, callable $callback): void {
            self::store($name, ['DELETE'], $uri, $callback);
        }

        /** Ajoute un middleware avant le traitement d'une route
         * @param string|null $routeName Nom de la route concernée, ou, null pour toutes les routes
         * @param callable $middleware Middleware à ajouter
         * @return void
         */
        public static function before(?string $routeName, callable|string $middleware): void {
            self::add('before', $routeName, $middleware);
        }

        /** Ajoute un middleware après le traitement d'une route
         * @param string|null $routeName Nom de la route concernée, ou, null pour toutes les routes
         * @param callable $middleware Middleware à ajouter
         * @return void
         */
        public static function after(?string $routeName, callable|string $middleware): void {
            self::add('after', $routeName, $middleware);
        }
    }

}