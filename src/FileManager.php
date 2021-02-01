<?php

namespace Frankie\Storage;

class FileManager
{
    public static function delete(FileInterface $file): bool
    {
        $path = $file->getFullPath();
        return unlink($path);
    }

    public static function copy(FileInterface $file, string $path): bool
    {
        return copy($file->getFullPath(), $path);
    }

    public static function move(FileInterface &$file, string $path): bool
    {
        $className = \get_class($file);
        if (rename($file->getFullPath(), $path)) {
            $file = new $className($path);
            return true;
        }
        return false;
    }

    public static function rename(FileInterface &$file, string $newName): bool
    {
        $extension = '';
        if ($file->getExtension() !== null) {
            $extension = '.' . $file->getExtension();
        }
        $className = \get_class($file);
        if (
        rename(
            $file->getFullPath(),
            $file->getDirName() . $newName . $extension
        )
        ) {
            $file = new $className($file->getDirName() . $newName . $extension);
            return true;
        }
        return false;
    }
}
