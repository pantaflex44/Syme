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

/** Retourne le nom d'hôte
 * @return string Nom d'hôte actuel
 */
function getHost(): string {
    $possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
    $sourceTransformations = array(
        'HTTP_X_FORWARDED_HOST' => function ($value) {
            $elements = explode(',', $value);
            return trim(end($elements));
        }
    );
    $host = '';
    foreach ($possibleHostSources as $source) {
        if (!empty($host)) {
            break;
        }
        if (empty($_SERVER[$source])) {
            continue;
        }
        $host = $_SERVER[$source];
        if (array_key_exists($source, $sourceTransformations)) {
            $host = $sourceTransformations[$source]($host);
        }
    }

    $host = preg_replace('/:\d+$/', '', $host);

    return trim($host);
}

/** Retourne l'adresse IP réelle actuelle
 * @return string Adresse IP
 */
function getRealIp(): string {
    return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['HTTP_X_FORWARDED'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_FORWARDED'] ?? $_SERVER['HTTP_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Arrête et nettoie le gestionnaire de session
 * @return void
 */
function clearSession(): void {
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

/** Démarre le gestionnaire de session
 * @return void
 */
function startSession(): void {
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

/** Adapte le type de la valeur passée en paramètre en fonction du type de sa valeur
 * @param string|array $value Valeur à adapter
 * @param bool $sanitize
 * @return array|string|int|bool|float|DateTime Valeur nouvellement typée
 */
function parse_value(string|array $value, bool $sanitize = false): array|string|int|bool|float|\DateTime {
    $entry = $value;
    if (!is_array($entry)) {
        $entry = [$entry];
    }

    $ret = [];
    foreach ($entry as $val) {
        $result = strval($val);
        if ($sanitize) {
            $result = htmlspecialchars($result);
        }
        $result = trim($result);

        if (strtolower($result) === 'true') {
            $result = true;
        } elseif (strtolower($result) === 'false') {
            $result = false;
        } elseif (is_numeric($result)) {
            if (ctype_digit(strval($result))) {
                $result = intval($result);
            } elseif (is_float($result + 0)) {
                $result = floatval($result);
            }
        } else {
            if ($result !== "") {
                try {
                    $result = new \DateTime($result);
                } catch (\Exception $e) {

                }
            }
        }

        $ret[] = $result;
    }

    if (count($ret) === 1 && !is_array($value)) {
        return $ret[0];
    }

    return $ret;
}

/** Retourne l'espace de nom et le nom de la classe présente dans un fichier PHP
 * @param string $file Chemin du fichier PHP
 * @return string Nom complet de la classe
 */
function get_classname_in_phpfile(string $file): string {
    $fp = fopen($file, 'r');

    $namespace = '';
    $class = '';
    $buffer = '';

    $i = 0;
    while (!$class) {
        if (feof($fp)) {
            break;
        }

        $buffer .= fread($fp, 512);
        $tokens = token_get_all($buffer);

        if (strpos($buffer, '{') === false) {
            continue;
        }

        for (; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j] === '{') {
                        if (is_array($tokens[$i + 2]) && count($tokens[$i + 2]) >= 1) {
                            $value = trim($tokens[$i + 2][1]);
                            if ($tokens[$i][0] === T_CLASS) {
                                $class = $value;
                            }
                            if ($tokens[$i][0] === T_NAMESPACE) {
                                $namespace = $value;
                            }
                        }
                    }
                }
            }
        }
    }

    fclose($fp);

    if ($namespace !== '') {
        $namespace .= '\\';
    }

    return $namespace . $class;
}
