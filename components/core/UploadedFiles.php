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
     * Liste des fichiers téléversés
     */
    class UploadedFiles {

        protected array $files = [];

        /**
         * Constructeur
         */
        public function __construct() {
            $this->files = [];

            foreach ($_FILES as $elementName => $file) {
                if (is_array($file['name'])) {
                    $this->files[$elementName] = [];

                    for ($i = 0; $i < count($file['name']); $i++) {
                        if (trim($file['name'][$i]) !== '') {
                            $this->files[$elementName][] = new UploadedFile([
                                'name' => $file['name'][$i],
                                'type' => $file['type'][$i],
                                'tmp_name' => $file['tmp_name'][$i],
                                'error' => $file['error'][$i],
                                'size' => $file['size'][$i],
                            ]);
                        }
                    }
                } else {
                    if (trim($file['name']) !== '')
                        $this->files[$elementName] = new UploadedFile($file);
                }
            }
        }

        /** Retourne la liste des fichiers téléversés
         * @return array Liste des fichiers téléversés sous forme d'un tableau associant le nom de l'élément file du formulaire et le fichier téléversé
         */
        public function getList(): array {
            return $this->files;
        }

        /** Retourne la présence d'un fichier téléversé en fonction du nom de l'élément fil du formulaire
         * @param string $elementName Nom de l'élément file
         * @return bool true, le fichier existe, sinon, false
         */
        public function hasFile(string $elementName): bool {
            return array_key_exists($elementName, $this->files);
        }

        /** Retourne le fichier téléversé
         * @param string $elementName Nom de l'élément file
         * @return UploadedFile|array|false Fichier téléchargé, ou liste de fichiers téléchargés, sinon, false en cas d'erreur
         */
        public function getFile(string $elementName): UploadedFile|array|false {
            if (!$this->hasFile($elementName))
                return false;

            return $this->files[$elementName];
        }

        /** Retourne le nombre de fichiers reçus
         * @return int Nombre de fichiers reçus
         */
        public function count(): int {

            function counter(array $list): int {
                $count = 0;

                foreach ($list as $name => $file) {
                    if (is_array($file)) {
                        $count += counter($file);
                    } else {
                        $count += 1;
                    }
                }

                return $count;
            }

            return counter($this->files);
        }

        public function __toString(): string {
            return 'Fichiers reçus: ' . $this->count();
        }
    }

}