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

/** Adapte le type de la valeur passée en paramètre en fonction du type de sa valeur
 * @param string|array $value Valeur à adapter
 * @param bool $sanitize
 * @return array|string|int|bool|float|DateTime Valeur nouvellement typée
 */
function parse_value(string|array $value, bool $sanitize = false): array|string|int|bool|float|DateTime {
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
                    $result = new DateTime($result);
                } catch (Exception $e) {
                    
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

/** DateInterval to seconds
 * @param DateInterval $interval
 * @return int Seconds
 */
function dateintervalToSeconds(DateInterval $interval): int {
    $daysInSecs = $interval->format('%r%a') * 24 * 60 * 60;
    $hoursInSecs = $interval->format('%r%h') * 60 * 60;
    $minsInSecs = $interval->format('%r%i') * 60;
    return $daysInSecs + $hoursInSecs + $minsInSecs + $interval->format('%r%s');
}

/** Supprimer un dossier et son contenu
 * @param string $dirname Dossier à supprimer
 * @return void
 */
function removeDir(string $dirname): void {
    if (is_dir($dirname)) {
        $dir = new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS);
        foreach (new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST) as $object) {
            if ($object->isFile()) {
                unlink(strval($object));
            } elseif ($object->isDir()) {
                rmdir(strval($object));
            }
        }
        rmdir($dirname);
    }
}

/** Copie un dossier et son contenu vers une destination
 * @param string $src Dossier à copier
 * @param string $dest Destination de la copie
 * @return void
 */
function copyDir(string $src, string $dest): void {
    foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
        if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
        } else {
            copy(strval($item), $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
        }
    }
}

/** Retourne le type mime d'un fichier
 * @param string $filepath Chemin du fichier 
 * @return string Type mime réel
 */
function getRealMimeType(string $filepath): string {
    $mimetype = mime_content_type($filepath);
    $mimetype = explode(';', $mimetype)[0];

    $infos = pathinfo($filepath);
    $extension = strtolower($infos['extension']);

    $reals = [
        'aac' => 'audio/aac',
        'abw' => 'application/x-abiword',
        'arc' => 'application/octet-stream',
        'avi' => 'video/x-msvideo',
        'azw' => 'application/vnd.amazon.ebook',
        'bin' => 'application/octet-stream',
        'bmp' => 'image/bmp',
        'bz' => 'application/x-bzip',
        'bz2' => 'application/x-bzip2',
        'csh' => 'application/x-csh',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'eot' => 'application/vnd.ms-fontobject',
        'epub' => 'application/epub+zip',
        'gif' => 'image/gif',
        'htm' => 'text/html',
        'html' => 'text/html',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'jar' => 'application/java-archive',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mpeg' => 'video/mpeg',
        'mpkg' => 'application/vnd.apple.installer+xml',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'oga' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'otf' => 'font/otf',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'rar' => 'application/x-rar-compressed',
        'rtf' => 'application/rtf',
        'sh' => 'application/x-sh',
        'svg' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        'tar' => 'application/x-tar',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'ts' => 'application/typescript',
        'ttf' => 'font/ttf',
        'vsd' => 'application/vnd.visio',
        'wav' => 'audio/x-wav',
        'weba' => 'audio/webm',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'xhtml' => 'application/xhtml+xml',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml' => 'application/xml',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'zip' => 'application/zip',
        '3gp' => 'video/3gpp',
        '3g2' => 'video/3gpp2',
        '7z' => 'application/x-7z-compressed'
    ];

    if (isset($reals[$extension]))
        return $reals[$extension];

    return $mimetype;
}

/** Retourne un nouveau jeton sécurisé
 * @return string Jeton sécurisé
 */
function secureToken(): string {
    $token = openssl_random_pseudo_bytes(16);
    $token = bin2hex($token);

    return $token;
}

/** Génère un mot de passe sécurisé
 * @param type $length Taille du mot de passe
 * @param type $add_dashes
 * @param type $available_sets
 * @return string
 */
function generateStrongPassword($length = 12, $add_dashes = false, $available_sets = 'luds'): string {
    $sets = array();
    if (strpos($available_sets, 'l') !== false)
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    if (strpos($available_sets, 'u') !== false)
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    if (strpos($available_sets, 'd') !== false)
        $sets[] = '23456789';
    if (strpos($available_sets, 's') !== false)
        $sets[] = '!@#$%&*?';

    $all = '';
    $password = '';
    foreach ($sets as $set) {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
    }

    $all = str_split($all);
    for ($i = 0; $i < $length - count($sets); $i++)
        $password .= $all[array_rand($all)];

    $password = str_shuffle($password);

    if (!$add_dashes)
        return $password;

    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while (strlen($password) > $dash_len) {
        $dash_str .= substr($password, 0, $dash_len) . '-';
        $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
}

/** Vérifie la solidité d'un mot de passe
 * @param string $password Mot de passe à vérifier
 * @param int $minLength Longueur du mot de passe désiré
 * @param bool $hasUppercase Possède au moins une majuscule
 * @param bool $hasLowercase Possède au moins une minuscule
 * @param bool $hasNumber Possède au moins un chiffre
 * @param bool $hasSpecial Possède au moins un caractère spécial
 * @return bool
 */
function passwordGoodStrength(string $password, int $minLength = 8, bool $hasUppercase = true, bool $hasLowercase = true, bool $hasNumber = true, bool $hasSpecial = true): bool {
    $password = trim($password);

    $uppercase = $hasUppercase ? preg_match('@[A-Z]@', $password) : true;
    $lowercase = $hasLowercase ? preg_match('@[a-z]@', $password) : true;
    $number = $hasNumber ? preg_match('@[0-9]@', $password) : true;
    $specialChars = $hasSpecial ? preg_match('@[^\w]@', $password) : true;

    return !(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < $minLength);
}

/** Retourne la taille maximale, en octets, admise pour le téléversement de fichiers
 * @return float
 */
function getFileUploadMaxSize(): float {
    $maxSize = -1;
    $parseSize = function (string $size): float {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    };

    $postMaxSize = $parseSize(ini_get('post_max_size'));
    if ($postMaxSize > 0) {
        $maxSize = $postMaxSize;
    }

    $uploadMax = $parseSize(ini_get('upload_max_filesize'));
    if ($uploadMax > 0 && $uploadMax < $maxSize) {
        $maxSize = $uploadMax;
    }

    return $maxSize;
}

function humanFilesize(float $bytes, int $dec = 2): string {
    $size = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo');
    $factor = floor((strlen(strval($bytes)) - 1) / 3);
    if ($factor == 0)
        $dec = 0;

    $sz = $bytes / (1024 ** $factor);
    $dec = floor($sz) === $sz ? 0 : $dec;

    return sprintf("%.{$dec}f %s", $sz, $size[$factor]);
}
