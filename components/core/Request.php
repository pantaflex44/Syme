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

    /**
     * Requète HTTP
     */
    class Request
    {

        protected string $fullUrl;

        protected string $url;

        protected string $path;

        protected string $uri;

        protected array $query;

        protected string $fragment;

        protected string $scheme;

        protected string $host;

        protected int $port;

        protected string $method;

        protected array $accepted_language;

        protected array $accepted_encoding;

        protected array $accepted_types;

        protected string $user_agent;

        protected string $remote_address;

        protected int $remote_port;

        protected array $headers;

        protected string $authorization;

        protected string $contentMimeType;

        protected string $content;

        protected null|object $form;

        protected UploadedFiles $files;

        protected string $referer = '';

        /** Retourne la requète courante
         * @return Request Requète courante
         */
        public static function current(): Request
        {
            return new Request();
        }

        /** Constructeur
         * @param string|null $url URL de la requète à créer
         */
        public function __construct(string $url = null)
        {
            $this->fullUrl = $url ?? (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]" . ($_SERVER['REQUEST_URI'] ?? '');
            if (str_ends_with($this->fullUrl, '/')) $this->fullUrl = substr($this->fullUrl, 0, strlen($this->fullUrl) - 1);
            $this->url = strtok($this->fullUrl, '?');
            $url = parse_url($this->fullUrl);
            $this->path = trim($url['path'] ?? '');
            if ($this->path === '') $this->path = '/';
            $this->uri = str_replace(ROOT_PATH, '', $this->path);
            if ($this->uri === '') $this->uri = '/';
            $this->fragment = $url['fragment'] ?? '';
            $this->scheme = strtolower($url['scheme'] ?? '');
            $this->host = $url['host'] ?? '';
            $this->port = intval($url['port'] ?? '0');
            parse_str($url['query'] ?? '', $query);
            $this->query = $query;
            $this->method = !is_null($url) ? strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET')) : 'GET';
            $this->accepted_language = array_map(fn($l) => trim($l), explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
            $this->accepted_encoding = array_map(fn($l) => strtolower(trim($l)), explode(',', $_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''));
            $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $this->remote_address = $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']);
            $this->remote_port = intval($_SERVER['REMOTE_PORT']);
            $types = explode(';', $_SERVER['HTTP_ACCEPT'] ?? '');
            if (count($types) > 0) $types = explode(',', $types[0]);
            $this->accepted_types = array_map(fn($t) => trim($t), $types);
            $this->headers = getallheaders();
            $this->authorization = $_SERVER['AUTHORIZATION'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
            $this->referer = $_SERVER['HTTP_REFERER'] ?? '';

            $this->contentMimeType = strtolower(trim($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? 'text/html'));
            $this->content = @file_get_contents("php://input");
            $this->files = new UploadedFiles();
            $this->form = null;
            if ($this->contentMimeType === "application/x-www-form-urlencoded") {
                parse_str($this->content, $contentParsed);
                $keys = is_array($contentParsed) ? array_keys($contentParsed) : [];
                if (count($keys) > 0) {
                    foreach ($keys as $key) $contentParsed[$key] = parse_value($contentParsed[$key], true);
                    $this->form = (object)$contentParsed;
                }
            }
        }

        /** Retourne le chemin de l'url
         * @return string Chemin de l'url
         */
        public function getPath(): string
        {
            return $this->path;
        }

        /** Retourne la portion d'url correspondante au chemin
         * @return string Portion d'url correspondante au chemin
         */
        public function getUri(): string
        {
            return $this->uri;
        }

        /** Retourne les paramètres d'url
         * @return array Paramètres d'url
         */
        public function getQueryString(): array
        {
            return $this->query;
        }

        /** Retourne le fragment
         * @return string Fragment
         */
        public function getFragment(): string
        {
            return $this->fragment;
        }

        /** Retourne le schéma HTTP
         * @return string Schéma HTTP
         */
        public function getScheme(): string
        {
            return $this->scheme;
        }

        /** Retroune l'adresse ip ou le nom d'hôte de la requète
         * @return string Adresse ip ou nom d'hôte de la requète
         */
        public function getHost(): string
        {
            return $this->host;
        }

        /** Retourne le numéro du port
         * @return int Numéro du port
         */
        public function getPort(): int
        {
            return $this->port;
        }

        /** Retourne le domaine et le chemin de la requète actuelle
         * @return string
         */
        public function getDomain(): string
        {
            $parsed = parse_url($this->url);
            return $parsed['scheme'] . '://' . $parsed['host'] . ':' . $parsed['port'] . $parsed['path'];
        }

        /** Retourne la méthode HTTP
         * @return string Méthode HTTP. (GET, POST, PUT, DELETE, OPTION, ...)
         */
        public function getMethod(): string
        {
            return $this->method;
        }

        /** Retourne la liste des langues acceptées
         * @return array Liste des langues acceptées
         */
        public function getAcceptedLanguage(): array
        {
            return $this->accepted_language;
        }

        /** Retourne la liste des encodage acceptés
         * @return array Liste des encodages acceptés
         */
        public function getAcceptedEncoding(): array
        {
            return $this->accepted_encoding;
        }

        /** Retourne la liste des types accesptés
         * @return array Liste des types acceptés
         */
        public function getAcceptedTypes(): array
        {
            return $this->accepted_types;
        }

        /** Retourne le User Agent
         * @return string User Agent
         */
        public function getUserAgent(): string
        {
            return $this->user_agent;
        }

        /** Retourne l'adresse ip de l'expéditeur
         * @return string Adresse ip de l'expéditeur
         */
        public function getRemoteAddress(): string
        {
            return $this->remote_address;
        }

        /** Retourne le numéro du port utilisé par l'expéditeur
         * @return int Numéro de port
         */
        public function getRemotePort(): int
        {
            return $this->remote_port;
        }

        /** Retourne l'url de la requète
         * @param bool $full true, url complétée des paramètres de la requète, sinon, false
         * @return string Url de la requète
         */
        public function getUrl(bool $full = false): string
        {
            return $full ? $this->fullUrl : $this->url;
        }

        /** Indique si un paramètre de la requète existe
         * @param string $name Nom du paramètre de la requète
         * @return bool true, le paramètre existe, sinon, false
         */
        public function hasArgument(string $name): bool
        {
            return in_array($name, array_keys($this->query));
        }

        /** Retourne un paramètre de la requète en fonction de son nom
         * @param string $name Nom du paramètre de la requète
         * @return false|string Valeur du paramètre, false en cas d'erreur
         */
        public function getArgument(string $name): false|string
        {
            if (!$this->hasArgument($name)) return false;

            return strval($this->query[$name]);
        }

        /** Indique si c'est une requète ajax
         * @return bool true, c'est une requète ajax, sinon, false
         */
        public function isXHRRequest(): bool
        {
            return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
        }

        /** Indique si une entète HTTP est présente
         * @param string $header Nom de l'entète HTTP
         * @return bool true, l'entète est présente, sinon, false
         */
        public function hasHeader(string $header): bool
        {
            return array_key_exists($header, $this->headers);
        }

        /** Retourne la liste des entètes HTTP
         * @return array Liste des entètes HTTP
         */
        public function getHeaders(): array
        {
            return $this->headers;
        }

        /** Retourne une entète HTTP par son nom
         * @param string $header Nom de l'entète HTTP
         * @return false|string Valeur de l'entère, false en cas d'erreur
         */
        public function getHeader(string $header): false|string
        {
            return $this->hasHeader($header) ? $this->headers[$header] : false;
        }

        /** Retourne le jeton d'autorisation
         * @return string Jeton d'autorisation
         */
        public function getAuthorization(): string
        {
            return $this->authorization;
        }

        /** Retourne le corps de la requète
         * @return string Corps de la requète
         */
        public function getContent(): mixed
        {
            return $this->content;
        }

        /** Retourne le type Mime du contenu de la requète
         * @return string
         */
        public function getContentType(): string
        {
            return $this->contentMimeType;
        }

        /** Retourne le contenu d'un formulaire
         * @return object|null
         */
        public function getForm(): null|object
        {
            return $this->form;
        }

        /** Retourne si la requète contient un formulaire HTML
         * @return bool
         */
        public function hasForm(): bool
        {
            return !is_null($this->form);
        }

        /** Retourne la liste des fichiers téléversés
         * @return UploadedFiles Liste des fichiers téléversés
         */
        public function getFiles(): UploadedFiles
        {
            return $this->files;
        }

        /** Retourne l'url de la page ayant executé cette requète
         * @return bool|string url de la page, sinon, false, en cas d'erreur
         */
        public function getReferer(): bool|string
        {
            return $this->referer === '' ? false : $this->referer;
        }

        public function __toString(): string
        {
            return $this->fullUrl;
        }
    }

}