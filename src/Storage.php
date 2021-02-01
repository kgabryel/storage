<?php

namespace Frankie\Storage;

use InvalidArgumentException;

class Storage implements StorageInterface
{
    protected string $path;

    public function __construct(string $path)
    {
        $path = rtrim(
                str_replace(
                    [
                        '\\',
                        '/'
                    ],
                    DIRECTORY_SEPARATOR,
                    $path
                ),
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            throw new InvalidArgumentException("Directory '$path' not exists.");
        }
        if (!is_dir($path)) {
            throw new InvalidArgumentException("'$path' isn't a directory.");
        }
        $this->path = $path;
    }

    /**
     * @return string[]
     */
    public function getDirectoriesList(): array
    {
        $directories = glob($this->path . '*', GLOB_ONLYDIR);
        foreach ($directories as $key => $directory) {
            $directories[$key] = str_replace($this->path, '', $directory);
        }
        return $directories;
    }

    /**
     * @return string[]
     */
    public function getFilesList(): array
    {
        $all = scandir($this->path, 1);
        $files = [];
        for ($i = 0; $i < \count($all) - 2; $i++) {
            if (is_file($this->path . $all[$i])) {
                $files[] = $all[$i];
            }
        }
        return $files;
    }

    /**
     * @return File[]
     */
    public function getFiles(): array
    {
        $files = [];
        foreach ($this->getFilesList() as $file) {
            $files[] = new File($this->path . $file);
        }
        return $files;
    }

    public function goTo(string $path): bool
    {
        $path = $this->path . trim(
                str_replace(
                    [
                        '/',
                        "\\"
                    ],
                    DIRECTORY_SEPARATOR,
                    $path
                ),
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR;
        if (!file_exists($path) || !is_dir($path)) {
            return false;
        }
        $this->path = $path;
        return true;
    }

    public function goBack(): bool
    {
        $position = strripos(rtrim($this->path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        if ($position !== false) {
            $this->path = substr($this->path, 0, $position) . DIRECTORY_SEPARATOR;
            return true;
        }
        return false;
    }

    public function createDirectory(string $name, bool $recursive = false, int $mode = 0777): bool
    {
        $name = trim(
            str_replace(
                [
                    '/',
                    "\\"
                ],
                DIRECTORY_SEPARATOR,
                $name
            ),
            DIRECTORY_SEPARATOR
        );
        return mkdir($this->path . $name, $mode, $recursive);
    }

    public function createFile(string $name, int $mode = 0777): bool
    {
        $name = trim(
            str_replace(
                [
                    '/',
                    "\\"
                ],
                DIRECTORY_SEPARATOR,
                $name
            ),
            DIRECTORY_SEPARATOR
        );
        if (touch($this->path . $name)) {
            return chmod($this->path . $name, $mode);
        }
        return false;
    }

    public function changeMode(string $path, int $mode): bool
    {
        $path = trim(
            str_replace(
                [
                    '/',
                    "\\"
                ],
                DIRECTORY_SEPARATOR,
                $path
            ),
            DIRECTORY_SEPARATOR
        );
        if (!file_exists($this->path . $path)) {
            throw new InvalidArgumentException("Path '$path' not exists.");
        }
        return chmod($this->path . $path, $mode);
    }

    public function delete(string $path): bool
    {
        $path = trim(
            str_replace(
                [
                    '/',
                    "\\"
                ],
                DIRECTORY_SEPARATOR,
                $path
            ),
            DIRECTORY_SEPARATOR
        );
        if (!file_exists($this->path . $path)) {
            throw new InvalidArgumentException("Path '$path' not exists.");
        }
        if (is_file($this->path . $path)) {
            return unlink($this->path . $path);
        }
        return $this->deleteDirectory($this->path . $path . DIRECTORY_SEPARATOR);
    }

    protected function deleteDirectory(string $path): bool
    {
        $all = scandir($path, 1);
        unset($all[\count($all) - 1], $all[\count($all) - 1]);
        foreach ($all as $item) {
            if (is_dir($path . $item)) {
                $this->deleteDirectory($path . $item . DIRECTORY_SEPARATOR);
            } else {
                unlink($path . $item);
            }
        }
        return rmdir($path);
    }

    public function getCurrentDirectory(): string
    {
        return $this->path;
    }
}
