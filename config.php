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

// Framework Syme
define('DEBUG', false);
define('CORE_NAME', 'Syme');
define('CORE_VERSION', '0.1.0-dev');
define('ROOT_PATH', dirname($_SERVER['SCRIPT_NAME']));
define('ASSETS_PATH', __DIR__ . '/public');
define('USE_COMPRESSION', true);
define('USE_CACHE', true);
define('CACHE_DELAY', 3600);
define('ASSETS_PACKET_SIZE', 1024);

// composant: Session
/* define('SESSION_USE_COOKIES', 1);
define('SESSION_USE_ONLY_COOKIES', 1);
define('SESSION_USE_STRICT_MODE', 1);
define('SESSION_COOKIE_HTTPONLY', 1);
define('SESSION_COOKIE_SECURE', 1);
define('SESSION_COOKIE_SAMESITE', 'Strict');
define('SESSION_USE_TRANS_ID', 0);
define('SESSION_CACHE_LIMITER', 'nocache');
define('SESSION_URL_REWRITER_TAGS', 0);
define('SESSION_LIFETIME', 0);
define('SESSION_COOKIE_PATH', ROOT_PATH); */

// composant: MySQL
/*define('MYSQL_HOST', 'localhost');
define('MYSQL_PORT', 3306);
define('MYSQL_DATABASE', 'syme');
define('MYSQL_USERNAME', 'user');
define('MYSQL_PASSWORD', '');*/

