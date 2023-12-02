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

    use components\Body;

    /**
     * Réponse HTTP
     */
    class Response
    {

        protected string $contentType;

        protected string $body;

        protected array $headers = [];

        protected int $status = 200;

        private function filter(array $parsed): array
        {
            $filtered = [];

            foreach ($parsed as $k => $v) {
                if (is_string($v)) {
                    $v = trim($v);

                    if (strtolower($v) === 'true') $filtered[$k] = true;
                    elseif (strtolower($v) === 'false') $filtered[$k] = false;
                    elseif (is_numeric($v) && is_float($v + 0)) $filtered[$k] = floatval($v);
                    elseif (is_numeric($v) && ctype_digit(strval($v))) $filtered[$k] = intval($v);
                    else $filtered[$k] = $v;
                } else {
                    $filtered[$k] = $v;
                }
            }

            return $filtered;
        }

        /** Constructeur
         * @param Body $body Contenu de la réponse
         */
        public function __construct(string $body = '', string $contentType = 'text/html')
        {
            $this->body = $body;
            $this->contentType = $contentType;
        }

        /** Indique si une entète HTTP est présente
         * @param string $header Nom de l'entète HTTP
         * @return bool true, l'entète est présente, sinon, false
         */
        public function hasHeader(string $header): bool
        {
            return array_key_exists($header, $this->headers);
        }

        /** Supprime une entète HTTP
         * @param string $headerKey Nom de l'entète
         * @return bool true, l'entète a été supprimée, sinon, false
         */
        public function removeHeader(string $headerKey): bool
        {
            $index = array_search(strtolower($headerKey), array_map('strtolower', array_keys($this->headers)));
            if ($index === false) return false;

            $key = array_keys($this->headers)[$index];
            unset($this->headers[$key]);

            return true;
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

        /** Ajoute une entète HTTP
         * @param string $header Entète HTTP à ajouter
         * @return $this
         */
        public function withHeader(string $header): Response
        {
            if (!$this->hasHeader($header)) $this->headers[] = $header;
            return $this;
        }

        /** Ajoute une entète HTTP d'authorization
         * @param string $bearer Jeton JWT
         * @return $this
         */
        public function withBearerAuthorization(string $bearer): Response
        {
            if (preg_match('/Bearer\s(\S+)/is', $bearer, $matches)) $bearer = $matches[1];
            return $this->withHeader("Authorization: Bearer $bearer");
        }

        /** Ajoute des entètes HTTP
         * @param string $header Entètes HTTP à ajouter
         * @return $this
         */
        public function withHeaders(array $headers): Response
        {
            foreach ($headers as $key => $value) $this->withHeader(ucfirst(strtolower($key)) . ": " . $value);
            return $this;
        }

        /** Retourne le code HTTP lié à la réponse
         * @return int Code HTTP
         */
        public function getStatus(): int
        {
            return $this->status;
        }

        /** Modifie le status de la réponse
         * @param int $status Code HTTP du status de la réponse
         * @return $this
         */
        public function withStatus(int $status): Response
        {
            if (
                ($status >= 100 && $status <= 103)
                || ($status >= 200 && $status <= 208)
                || $status === 226
                || ($status >= 300 && $status <= 308)
                || $status === 310
                || ($status >= 400 && $status <= 419)
                || $status === 431
                || ($status >= 449 && $status <= 451)
                || $status === 456
                || $status === 444
                || ($status >= 495 && $status <= 499)
                || ($status >= 500 && $status <= 511)
                || ($status >= 520 && $status <= 527)
            ) {
                $this->status = $status;
            }

            return $this;
        }

        /**  Renvoie le contenu brut
         * @return string Contenu brut
         */
        public function getContent(): string
        {
            return $this->body;
        }

        public function getGzipContent(): string
        {
            return gzencode(trim(preg_replace('/\s+/', ' ', $this->body)), 9);
        }

        /** Retourne le type du contenu
         * @return string Type du contenu
         */
        public function getContentType(): string
        {
            return $this->contentType;
        }

        /** Renvoie le contenu découpé et analysé
         * @return array Contenu découpé
         */
        public function getParsed(): array
        {
            try {
                switch (strtolower(trim(explode(';', $this->contentType)[0]))) {
                    case 'application/x-www-form-urlencoded':
                        parse_str($this->body, $parsed);
                        return $this->filter($parsed);
                    case 'application/json':
                        $parsed = @json_decode($this->body, true) ?? [];
                        return $this->filter($parsed);
                    case 'application/xml':
                    case 'text/xml':
                        $xml = simplexml_load_string($this->body, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NOWARNING);
                        if ($xml === false) return [];
                        return @json_decode(@json_encode($xml),true) ?? [];
                    case 'multipart/form-data':
                        $method = strtoupper(trim($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? $_SERVER['REQUEST_METHOD'] ?? ''));
                        if (!in_array($method, ['PUT', 'DELETE', 'PATCH']) || preg_match('/^multipart\/form-data; boundary=.*$/ui', $this->contentType) !== 1) return [];

                        $formData = $this->body;
                        $boundary = preg_replace('/(^multipart\/form-data; boundary=)(.*$)/ui', '$2', $this->contentType);

                        if (preg_match('/^\s*--'.$boundary.'.*\s*--'.$boundary.'--\s*$/muis', $formData) !== 1) return [];
                        $formData = preg_replace('/(^\s*--'.$boundary.'.*)(\s*--'.$boundary.'--\s*$)/muis', '$1', $formData);
                        $formData = preg_split('/\s*--'.$boundary.'\s*Content-Disposition: form-data;\s*/muis', $formData, 0, PREG_SPLIT_NO_EMPTY);

                        $parsedData = [];
                        foreach ($formData as $field) {
                            $name =  preg_replace('/(name=")(?<name>[^"]+)("\s*)(?<value>.*$)/mui', '$2', $field);
                            $value =  preg_replace('/(name=")(?<name>[^"]+)("\s*)(?<value>.*$)/mui', '$4', $field);

                            if (str_contains($name, '[')) {
                                $keys = explode('[', trim($name));
                                $name = '';
                                foreach ($keys as $key) $name .= '{"' . rtrim($key, ']') . '":';
                                $name .= '"' . trim($value) . '"' . str_repeat('}', count($keys));
                                $array = json_decode($name, true);
                                if (!is_null($array)) $parsedData = array_replace_recursive($parsedData, $array);
                            } else {
                                $parsedData[trim($name)] = trim($value);
                            }
                        }

                        return $this->filter($parsedData);
                    default:
                        return [];
                }
            } catch (\Exception $ex) {
                return [];
            }
        }

        /** Renvoie le contenu au format JSON
         * @param bool $associative true, renvoie un tableau associatif, sinon, false pour renvoyer un objet
         * @return mixed Contenu au format JSON
         */
        public function getJson(bool $associative = true): mixed
        {
            try {
                return json_decode($this->body, $associative);
            } catch (\Exception $ex) {
                return $this->body;
            }
        }

        /** Enregistre un nouveau contenu
         * @param string $content Content to write
         * @return $this This instance
         */
        public function write(string $content, string $contentType = 'text/html'): Response
        {
            $this->body .= $content;
            $this->contentType = $contentType;
            return $this;
        }

        /** Ajoute au début du contenu
         * @param string $content Content à écrire
         * @return $this
         */
        public function prepend(string $content): Response
        {
            $this->body = $content . $this->body;
            return $this;
        }

        /** Ajoute à la fin du contenu
         * @param string $content Content à écrire
         * @return $this
         */
        public function append(string $content): Response
        {
            $this->body .= $content;
            return $this;
        }

        /** Ecrit du contenu en convertissant un objet passé en paramètre au format JSON
         * @param mixed $object Objet a convertir
         * @return $this This instance
         */
        public function writeObject(mixed $object): Response
        {
            $this->body = json_encode($object, JSON_PRETTY_PRINT);
            $this->contentType = 'application/json';
            return $this;
        }

        /** Ecrit du contenu au format JSON
         * @param mixed $json Contenu au format JSON à écrire
         * @return $this This instance
         */
        public function writeJson(string $json): Response
        {
            $this->body = $json;
            $this->contentType = 'application/json';
            return $this;
        }

        public function __toString(): string
        {
            return $this->body;
        }

    }

}