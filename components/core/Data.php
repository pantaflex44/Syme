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
     * Liste des données personnelles liées aux routes
     */
    class Data {

        protected array $data = [];

        /**
         * Constructeur
         */
        public function __construct() {
            $this->data = [];
        }

        /** Retourne le nombre de données enregistrées
         * @return int Nombre de données enregistrées
         */
        public function count(): int {
            return count($this->data);
        }

        /** Supprime toutes les données
         * @return void
         */
        public function clear(): void {
            $this->data = [];
        }

        /** Retourne la liste complète des données
         * @return array Liste des données
         */
        public function all(): array {
            return $this->data;
        }

        /** Vérifie si un paramètre existe
         * @param string $key Nom du paramètre
         * @return bool
         */
        public function exists(string $key): bool {
            return isset($this->data[$key]);
        }

        /** Retourne la valeur d'un paramètre
         * @param string $key Nom du paramètre
         * @return mixed
         */
        public function get(string $key): mixed {
            if (!isset($this->data[$key]))
                return null;

            return $this->data[$key];
        }

        /** Définit la valeur d'un paramètre
         * @param string $key Nom du paramètre
         * @param mixed $value Valeur du paramètre
         * @return void
         */
        public function set(string $key, mixed $value): void {
            $this->data[$key] = $value;
        }

        /** Détruit un paramètre
         * @param string $key Nom du paramètre
         * @return void
         */
        public function delete(string $key): void {
            if (isset($this->data[$key]))
                unset($this->data[$key]);
        }

        public function __toString(): string {
            return json_encode(ksort($this->data), JSON_PRETTY_PRINT);
        }
    }

}