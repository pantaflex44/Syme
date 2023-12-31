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
     * Fichier uploadé
     */
    class UploadedFile {

        protected string $filename;
        protected string $filetype;
        protected string $tmp_name;
        protected int $filesize;
        protected int $error;

        /** Constructeur
         * @param array $fileinfo Tableau contenant les informations du fichier uploadé
         */
        public function __construct(array $fileinfo) {
            $this->filename = $fileinfo['name'] ?? '';
            $this->filetype = $fileinfo['type'] ?? '';
            $this->filesize = intval($fileinfo['size'] ?? '0');
            $this->tmp_name = $fileinfo['tmp_name'] ?? '';
            $this->error = $fileinfo['error'] ?? UPLOAD_ERR_OK;
        }

        /** Retourne le nom du fichier
         * @return string Nom du fichier
         */
        public function getName(): string {
            return $this->filename;
        }

        public function getExtension(): string {
            $infos = pathinfo($this->filename);
            $extension = strtolower($infos['extension']);
            return $extension;
        }

        /** Retourne le type mime du fichier
         * @return string Type mime du fichier
         */
        public function getType(): string {
            return $this->filetype;
        }

        /** Retourne la taille en octets du fichier
         * @return int Taille en octets du fichier
         */
        public function getSize(): int {
            return $this->filesize;
        }

        /** Retourne la taille du fichier au format lisible
         * @return string Taille lisible du fichier
         */
        public function getReadableSize(): string {
            $size = $this->getSize();

            if ($size === 0)
                return "0.00o";

            $s = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po');
            $e = floor(log($size, 1024));

            return round($size / pow(1024, $e), 2) . $s[$e];
        }

        /** Retourne le type du fichier
         * @return false|string Type du fichier, false en cas d'erreur
         */
        public function getContentType(): false|string {
            try {
                return getRealMimeType($this->tmp_name);
            } catch (\Exception $ex) {
                return false;
            }
        }

        /** Retourne le code erreur du versement
         * @return int Code erreur
         */
        public function getError(): int {
            return $this->error;
        }

        /** Retourne si le versement du fichier a provoqué une erreur
         * @return bool true, une erreur est apparue, sinon, false
         */
        public function hasError(): bool {
            return $this->getError() !== UPLOAD_ERR_OK;
        }

        /** Déplace le fichier uploadé à sa place finale
         * @param string $directory Chemin du dossier qui sera le receptable du fichier versé
         * @return bool true, le déplacement a été éxécuté avec succès, sinon, false
         */
        public function moveTo(string $directory, string $filename = null): bool {
            try {
                $to = $directory . DIRECTORY_SEPARATOR . ($filename ?? $this->tmp_name);
                return move_uploaded_file($this->tmp_name, $to);
            } catch (\Exception $ex) {
                return false;
            }
        }

        public function __toString(): string {
            return $this->getName() . ' | ' . $this->getReadableSize() . ' | ' . ($this->getContentType() ?: 'Aucun type connu');
        }
    }

}