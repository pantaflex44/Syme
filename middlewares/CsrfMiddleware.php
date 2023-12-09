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

namespace middlewares {

    use components\core\Request;
    use components\extended\Session;
    use components\extended\TwigWrapper;

    /**
     * Gestion des attaques CSRF
     *
     * Requiert:
     *  Composant: 'components\extended\Session'
     *  Composant: 'components\core\Request'
     */
    class CsrfMiddleware {

        /** Se produit lorque le middleware est ajouté à une route
         * @param Session $session Gestionnaire de sessions
         * @return void
         */
        public static function __added(Session $session): void {
            TwigWrapper::addFunction('csrf', function (string $prefix = '', int $length = 32) use ($session): string {
                $name = uniqid($prefix);
                $token = bin2hex(random_bytes($length));
                $session->set('csrf_token', ['name' => $name, 'token' => $token]);

                $html = '<input type="hidden" name="csrf_name" value="' . $name . '" /><input type="hidden" name="csrf_token" value="' . $token . '" />';
                return $html;
            });
        }

        /** Fonction exécutée lors de l'utilisation d'un middleware
         * @param Request $request Dernière requète
         * @param Session $session Gestionnaire de sessions
         * @return void
         */
        public function __invoke(Request $request, Session $session): void {
            if ($request->hasForm()) {
                $form = $request->getForm();

                if (!isset($form->csrf_name) || !isset($form->csrf_token) || !$session->exists('csrf_token')) {
                    header('HTTP/1.0 403 Forbidden');
                    exit;
                }

                $csrf = $session->get('csrf_token');

                if (!isset($csrf['name']) || $csrf['name'] !== $form->csrf_name) {
                    header('HTTP/1.0 401 Unauthorized');
                    exit;
                }

                if (!isset($csrf['token']) || $csrf['token'] !== $form->csrf_token) {
                    header('HTTP/1.0 401 Unauthorized');
                    exit;
                }
            }

            $session->delete('csrf_token');
        }
    }

}