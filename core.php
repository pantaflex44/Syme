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

$configFile = __DIR__ . '/config.local.php';
if (!file_exists($configFile) || !is_readable($configFile)) {
    $configFile = __DIR__ . '/config.php';
}
require_once $configFile;
require_once __DIR__ . '/functions.php';

$vendorAutoloader = __DIR__ . '/vendor';
if (is_dir($vendorAutoloader)) {
    require_once $vendorAutoloader . '/autoload.php';
}

use components\core\Request;
use components\core\Route;

ini_set('default_charset', 'UTF-8');
ini_set('error_prepend_string', '<pre>');
ini_set('error_append_string', '</pre>');
ini_set('html_errors', true);
ini_set('log_errors', true);
ini_set('error_log', realpath(__DIR__ . 'error.log'));
ini_set('display_errors', DEBUG);
ini_set('display_startup_errors', DEBUG);
error_reporting(DEBUG ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_trans_id', 0);
ini_set('session.cache_limiter', 'nocache');
ini_set('session.url_rewriter_tags', 0);
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_path', '/');
startSession();

$import = function (array $directories): void {
    foreach ($directories as $directory) {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $directory;
        if (!is_dir($dir) || !is_readable($dir)) {
            continue;
        }

        $requiredMethods = [];

        // on recherche tous les fichiers à inclure
        $subd = new \RecursiveDirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . $directory);
        $iterator = new \RecursiveIteratorIterator($subd);
        $files = new \RegexIterator($iterator, '/^.+\.(php|inc)$/i', RegexIterator::MATCH);
        foreach ($files as $file) {
            // inclut le fichier PHP
            require_once $file->getPathname();

            // recherche le nom complet de la classe dans le fichier inclut précédement
            // puis recherche si la fonction statique __required est présente. Si oui, on mémorise
            // la structure permettant de l'invoquer une fois tous les fichiers inclus.
            try {
                $class = new ReflectionClass(get_classname_in_phpfile($file->getPathname()));
                if ($class->hasMethod('__required')) {
                    $requiredMethods[] = [
                        'method' => $class->getMethod('__required'),
                        'class' => $class
                    ];
                }
            } catch (\Exception $ex) {

            }
        }

        // on appelle toutes les fonctions statiques __required trouvées dans les fichiers inclus
        foreach ($requiredMethods as $obj) {
            $obj['method']->invoke($obj['class']);
        }
    }
};
$import([
    'components',
    'middlewares',
    'controllers'
]);

register_shutdown_function(
        /**
         * @throws ReflectionException
         */
        function () {
            header('X-Powered-By: ' . CORE_NAME . '/' . CORE_VERSION . ' (Php ' . PHP_VERSION . ')');

            $currentRequest = Request::current();

            $filepath = Route::isAsset($currentRequest);
            if ($filepath !== false) {
                Route::sendAsset($filepath);
            } else {
                list($request, $response) = Route::apply($currentRequest);
                Route::sendResponse($request, $response);
            }
        }
);
