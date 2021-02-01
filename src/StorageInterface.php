<?php

namespace Frankie\Storage;

interface StorageInterface
{
    /**
     * @return string[]
     */
    public function getDirectoriesList(): array;

    /**
     * @return string[]
     */
    public function getFilesList(): array;

    /**
     * @return File[]
     */
    public function getFiles(): array;

    public function goTo(string $path): bool;

    public function goBack(): bool;

    public function createDirectory(string $name, bool $recursive = false, int $mode = 0777): bool;

    public function createFile(string $name, int $mode = 0777): bool;

    public function changeMode(string $path, int $mode): bool;

    public function delete(string $path): bool;

    public function getCurrentDirectory(): string;
}
