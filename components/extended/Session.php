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

    class Session {

        /** Se produit lorsque le composant est chargé
         * @return void
         */
        public static function __required(): void {
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

            Route::extendWith(Session::class);
        }

        public function __construct() {
            session_name(CORE_NAME);
            session_start();

            $valid = true;

            if (isset($_SESSION['REMOTE_ADDR'])) {
                $valid &= ($_SESSION['REMOTE_ADDR'] == getRealIp());
            } else {
                $_SESSION['REMOTE_ADDR'] = getRealIp();
            }

            if (isset($_SESSION['HTTP_USER_AGENT'])) {
                $valid &= ($_SESSION['HTTP_USER_AGENT'] == $_SERVER['HTTP_USER_AGENT']);
            } else {
                $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            }

            if (!$valid) {
                clearSession();
                startSession();
                exit;
            }
        }

        /** Arrête et nettoie le gestionnaire de session
         * @return void
         */
        public function destroy(): void {
            $_SESSION = [];

            $params = session_get_cookie_params();
            setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
            );

            session_unset();
            session_destroy();
        }

        /** Vérifie si un paramètre existe
         * @param string $name Nom du paramètre
         * @return bool
         */
        public function __isset(string $name): bool {
            return isset($_SESSION[$name]);
        }

        /** Retourne la valeur d'un paramètre
         * @param string $name Nom du paramètre
         * @return mixed
         */
        public function __get(string $name): mixed {
            if (!isset($_SESSION[$name]))
                return null;

            return $_SESSION[$name];
        }

        /** Définit la valeur d'un paramètre
         * @param string $name Nom du paramètre
         * @param mixed $value Valeur du paramètre
         * @return void
         */
        public function __set(string $name, mixed $value): void {
            $_SESSION[$name] = $value;
        }

        /** Détruit un paramètre
         * @param string $name Nom du paramètre
         * @return void
         */
        public function __unset(string $name): void {
            if (isset($_SESSION[$name]))
                unset($_SESSION[$name]);
        }
    }

}