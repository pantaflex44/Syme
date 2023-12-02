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
    class Data
    {

        protected array $data = [];

        /**
         * Constructeur
         */
        public function __construct()
        {
            $this->data = [];
        }

        /** Retourne le nombre de données enregistrées
         * @return int Nombre de données enregistrées
         */
        public function count(): int
        {
            return count($this->data);
        }

        /** Ajoute une donnée personnelle
         * @param string $key Nom de la donnée
         * @param mixed $value Valeur de la donnée
         * @return void
         */
        public function set(string $key, mixed $value): void
        {
            $this->data[$key] = $value;
        }

        /** Récupère la valeur d'une donnée par son nom
         * @param string $key Nom de la donnée
         * @return mixed Valeur de la donnée, null, si elle n'éxiste pas
         */
        public function get(string $key): mixed
        {
            return $this->data[$key] ?? null;
        }

        /** Indique si une donnée existe par son nom
         * @param string $key Nom de la donnée
         * @return bool true, la donnée existe, sinon, false
         */
        public function exists(string $key): bool
        {
            return isset($this->data[$key]);
        }

        /** Supprime une donnée par son nom
         * @param string $key Nom de la donnée
         * @return bool true, la donnée est bien supprimée, sinon, false
         */
        public function delete(string $key): bool
        {
            if ($this->exists($key)) {
                unset($this->data[$key]);
                return true;
            }

            return false;
        }

        /** Supprime toutes les données
         * @return void
         */
        public function clear(): void
        {
            $this->data = [];
        }

        /** Retourne la liste complète des données
         * @return array Liste des données
         */
        public function all(): array
        {
            return $this->data;
        }

        public function __toString(): string
        {
            return json_encode(ksort($this->data), JSON_PRETTY_PRINT);
        }

    }

}