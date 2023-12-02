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

namespace middlewares {

    use components\core\Data;
    use components\core\Request;
    use components\core\Response;

    /**
     * Interface d'un middleware
     */
    class CsrfMiddleware
    {

        /** Fonction exécutée lors de l'utilisation d'un middleware
         * @param Request $request Dernière requète
         * @param Response $response Dernière réponse
         * @param Data $data Données personnelles
         * @return void
         */
        public function __invoke(array $attributes, Request $request, Response $response, Data $data): void
        {
            $data->set('bob', 'est cool');
            $data->set('crsf middleware', $attributes);
            $response->withHeader('X-bob: cool');
        }

    }

}