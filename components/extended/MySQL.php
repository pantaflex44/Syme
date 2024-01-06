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

    use components\core\Route;

    /**
     * Gestionnaire de données via Php PDO
     *
     * Requiert:
     *  Configuration: MYSQL_HOST (impératif)
     *  Configuration: MYSQL_PORT (impératif)
     *  Configuration: MYSQL_DATABASE (impératif)
     *  Configuration: MYSQL_USERNAME (impératif)
     *  Configuration: MYSQL_PASSWORD (impératif)
     */
    class MySQL {

        /** Se produit lorsque le composant est chargé
         * @return void
         */
        public static function __required(): void {
            if (!defined('MYSQL_HOST'))
                throw new \Exception("MYSQL_HOST parameter not defined in config file.");
            if (!defined('MYSQL_PORT'))
                throw new \Exception("MYSQL_PORT parameter not defined in config file.");
            if (!defined('MYSQL_USERNAME'))
                throw new \Exception("MYSQL_USERNAME parameter not defined in config file.");
            if (!defined('MYSQL_PASSWORD'))
                throw new \Exception("MYSQL_PASSWORD parameter not defined in config file.");
            if (!defined('MYSQL_DATABASE'))
                throw new \Exception("MYSQL_DATABASE parameter not defined in config file.");

            Route::extendWith(MySQL::class);
        }

        protected ?\PDO $instance = null;

        /** Charge une nouvelle connexion à la base de données
         * @return void
         */
        private function connect(): void {
            try {
                $cs = sprintf('mysql:host=%s;port=%d;charset=utf8', MYSQL_HOST, MYSQL_PORT);

                $opts = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_FOUND_ROWS => true,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_STRINGIFY_FETCHES => false,
                ];

                $pdo = new \PDO($cs, MYSQL_USERNAME, MYSQL_PASSWORD, $opts);

                $stmt = $pdo->query(sprintf("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s';", MYSQL_DATABASE));
                $exists = (bool) $stmt->fetchColumn();
                if (!$exists) {
                    $pdo->exec(sprintf("CREATE DATABASE IF NOT EXISTS %s CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;", MYSQL_DATABASE));
                    $pdo->exec("DEFAULT CHARACTER SET utf8mb4;");
                    $pdo->exec("DEFAULT COLLATE utf8_general_ci;");
                    $pdo->exec("SET default_storage_engine = InnoDB;");
                }

                $pdo->exec(sprintf("USE %s;", MYSQL_DATABASE));

                $this->instance = $pdo;
            } catch (Exception $ex) {
                $this->instance = null;

                throw new \Exception("Unable to load new PDO instance.");
            }
        }

        public function __construct() {
            $this->connect();
        }

        public function __destruct() {
            $this->instance = null;
        }

        /** Retourne une instance de l'objet PDO
         * @return \PDO
         */
        public function getInstance(): \PDO {
            return $this->instance;
        }

        /** Charge un fichier SQL
         * @param string $sqlFile Fichier SQL à charger
         * @return void
         */
        public function loadSqlFile(string $sqlFile): void {
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $this->instance->exec($sql);
            }
        }

        /** Assainie une valeur
         * @param mixed $value Valeur à assainir
         * @return string
         */
        public function quote(mixed $value): string {
            return $this->instance->quote(strval($value));
        }

        /** Execute une requète
         * @param string $sql Requète SQL à éxecuter. (eg: INSERT INTO table (id) VALUES (0) )
         * @param array|null $args Tableau d'arguments de la requète
         * @return int Nombre d'enregistrements concernées par les changements
         */
        public function execute(string $sql, ?array $args = null, &$id = false): int {
            try {
                $stmt = $this->instance->prepare($sql);
                $stmt->execute($args);
                
                $id = $this->instance->lastInsertId();

                return $stmt->rowCount();
            } catch (\Exception $ex) {
                return 0;
            }
        }

        /** Retourne le nombre d'enregistrements trouvés
         * @param string $sql Requète SQL à éxecuter. (eg: SELECT COUNT(*) AS count FROM ...)
         * @param array|null $args Tableau d'arguments de la requète
         * @return int Quantité retournée
         */
        public function count(string $sql, ?array $args = null): int {
            try {
                $stmt = $this->instance->prepare($sql);
                $stmt->execute($args);

                return $stmt->fetchColumn();
            } catch (\Exception $ex) {
                return 0;
            }
        }

        /** Retourne l'unique enregistrement trouvé
         * @param string $sql Requète SQL à éxecuter. (eg: SELECT * FROM ...)
         * @param array|null $args Tableau d'arguments de la requète
         * @return array|bool false, en cas d'erreur ou si aucun ou plusieurs enregistrements ont été trouvés, sinon, retourne le premier enregistrement trouvé
         */
        public function unique(string $sql, ?array $args = null): array|bool {
            try {
                $stmt = $this->instance->prepare($sql);
                $stmt->execute($args);
                $result = $stmt->fetchAll() ?? [];

                if (count($result) !== 1)
                    return false;

                return $result[0];
            } catch (\Exception $ex) {
                return false;
            }
        }

        /** Retourne le premier enregistrement trouvé
         * @param string $sql Requète SQL à éxecuter. (eg: SELECT * FROM ...)
         * @param array|null $args Tableau d'arguments de la requète
         * @return array|bool false, en cas d'erreur ou si aucun enregistrement n'a été trouvé, sinon, retourne le premier enregistrement trouvé
         */
        public function first(string $sql, ?array $args = null): array|bool {
            try {
                $stmt = $this->instance->prepare($sql);
                $stmt->execute($args);
                $result = $stmt->fetchAll() ?? [];

                if (count($result) === 0)
                    return false;

                return $result[0];
            } catch (\Exception $ex) {
                return false;
            }
        }

        /** Retourne tous les enregistrements trouvés
         * @param string $sql Requète SQL à éxecuter. (eg: SELECT * FROM ...)
         * @param array|null $args Tableau d'arguments de la requète
         * @return array Tous les enregistrements trouvés
         */
        public function all(string $sql, ?array $args = null): array {
            try {
                $stmt = $this->instance->prepare($sql);
                $stmt->execute($args);

                return $stmt->fetchAll() ?? [];
            } catch (\Exception $ex) {
                return [];
            }
        }
        
        /** Retourne le nombre d'enregistrements supprimés
         * @param string $sql Requète SQL à éxecuter. (eg: SELECT * FROM ...)
         * @param array|null $args Tableau d'arguments de la requète
         * @return int Nombre d'enregistrements supprimés
         */
        public function delete(string $sql, ?array $args = null): int {
            try {
                $stmt = $this->instance->prepare($sql);
                $stmt->execute($args);

                return $stmt->rowCount();
            } catch (\Exception $ex) {
                return 0;
            }
        }
    }

}