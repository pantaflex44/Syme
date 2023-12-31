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

namespace components\extended {

    use components\core\Response;
    use components\core\Route;
    use Composer\InstalledVersions;
    use Exception;
    use Twig\Environment;
    use Twig\Error\LoaderError;
    use Twig\Error\RuntimeError;
    use Twig\Error\SyntaxError;
    use Twig\Loader\FilesystemLoader;
    use Twig\TwigFilter;
    use Twig\TwigFunction;
    use const DEBUG;
    use const ROOT_PATH;

    /**
     * Permet l'utilisation de la librairie Twig avec Syme.
     *
     * Requiert:
     *  Composer: twig/twig:^3.0
     */
    class TwigWrapper {

        private Environment $twig;
        private Response $response;
        protected static array $filters = [];
        protected static array $functions = [];
        protected array $params = [];

        /** Se produit lorsque le composant est chargé
         * @return void
         */
        public static function __required(): void {
            Route::extendWith(TwigWrapper::class);
        }

        /** Ajoute un filtre Twig
         * @param string $name Nom du filtre
         * @param callable $callback Fonction PHP à exécuter
         * @param array $options Tableau d'options complémentaires
         * @return void
         */
        public static function addFilter(string $name, callable $callback, array $options = []): void {
            $filter = new TwigFilter($name, $callback, $options);
            self::$filters[] = $filter;
        }

        /** Ajoute une fonction Twig
         * @param string $name Nom de la fonction
         * @param callable $callback Fonction PHP à exécuter
         * @param array $options Tableau d'options complémentaires
         * @return void
         */
        public static function addFunction(string $name, callable $callback, array $options = []): void {
            $function = new TwigFunction($name, $callback, $options);
            self::$functions[] = $function;
        }

        /** Constructeur
         * @param Response $response
         * @throws Exception
         */
        public function __construct(Response $response, Route $route) {
            if (!InstalledVersions::isInstalled('twig/twig')) {
                throw new Exception("Twig package not loaded. Please run 'composer install' before use your application.");
            } else {
                $twigVersion = InstalledVersions::getVersion('twig/twig');
                if (!version_compare($twigVersion, '3.0.0.0', '>=')) {
                    throw new Exception("Bad version of Twig package. More or equals than 3.0.0.0 expected.");
                }
            }

            $templatePath = defined('TWIG_TEMPLATE_PATH') ? constant('TWIG_TEMPLATE_PATH') : __DIR__ . '/../../templates';
            if (!is_dir($templatePath)) {
                mkdir($templatePath, 0744);
            }
            if (!is_readable($templatePath)) {
                throw new Exception("No readable template path.");
            }
            if (!is_writable($templatePath)) {
                throw new Exception("No writable template path.");
            }

            $cachePath = defined('TWIG_CACHE_PATH') ? constant('TWIG_CACHE_PATH') : $templatePath . '/cache';
            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0744);
            }
            if (!is_readable($cachePath)) {
                throw new Exception("No readable cache path.");
            }
            if (!is_writable($cachePath)) {
                throw new Exception("No writable cache path.");
            }

            $loader = new FilesystemLoader($templatePath);
            $this->twig = new Environment($loader, [
                'cache' => DEBUG ? false : $cachePath,
                'debug' => DEBUG,
                'charset' => 'utf-8'
            ]);

            $this->twig->addFilter(new TwigFilter('length',
                            function (mixed $value): int {
                                if (is_countable($value) || is_iterable($value))
                                    return count($value);
                                if (is_string($value))
                                    return strlen($value);
                                return 0;
                            })
            );

            $this->twig->addFilter(new TwigFilter('pluralize',
                            function (mixed $count, string $singular, string $plural, string $zero = null): string {
                                $count = is_null($count) ? 0 : intval($count);
                                if ($count > 1) {
                                    return str_replace('{}', strval($count), $plural);
                                } else if ($count <= 0 && !is_null($zero)) {
                                    return $zero; // No string replacement required for zero
                                }
                                return str_replace('{}', strval($count), $singular);
                            })
            );

            $this->twig->addFilter(new TwigFilter('frenchPhoneFormatter',
                            function (string $value): string {
                                return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1 \2 \3 \4 \5', $value);
                            })
            );

            $this->twig->addFunction(new TwigFunction('getUrl', function (string $routeName, array $params = []) use ($route): string {
                                return $route::getUrl($routeName, $params) ?: '';
                            }));

            foreach (TwigWrapper::$filters as $filter) {
                $this->twig->addFilter($filter);
            }
            foreach (TwigWrapper::$functions as $function) {
                $this->twig->addFunction($function);
            }

            $this->response = $response;
        }

        /** Définit un paramètre global
         * @param string $key Nom de la clef
         * @param mixed $value Valeur de la clef
         * @return void
         */
        public function setParam(string $key, mixed $value): void {
            $this->params[$key] = $value;
        }

        /** Supprime un paramètre
         * @param string $key Nom de la clef
         * @return void
         */
        public function deleteParam(string $key): void {
            if (isset($this->params[$key]))
                unset($this->params[$key]);
        }

        /** Compile et rend le contenu d'un modèle
         * @param string $templateName Nom du modèle
         * @param array $data Données à intégrer
         * @return Response
         * @throws LoaderError
         * @throws RuntimeError
         * @throws SyntaxError
         */
        public function createResponse(string $templateName, array $data = [], bool $toCurrentResponse = true): Response {
            $data = [...$this->params, ...$data, 'ROOT_PATH' => ROOT_PATH];

            $content = $this->twig->render($templateName, $data);

            if ($toCurrentResponse) {
                $this->response
                        ->clear()
                        ->write($content);

                return $this->response;
            } else {
                return new Response($content, 'text/html');
            }
        }

        /** Compile le contenu d'un modèle et renvoie son contenu
         * @param string $templateName Nom du modèle
         * @param array $data Données à intégrer
         * @return string
         */
        public function toString(string $templateName, array $data = []): string {
            $data = [...$data, ...$this->params, 'ROOT_PATH' => ROOT_PATH];

            $content = $this->twig->render($templateName, $data);
            return $content;
        }
    }

}