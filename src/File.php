<?php

namespace Frankie\Storage;

use DateTime;
use finfo;
use Frankie\Response\Response;
use Frankie\Response\ResponseInterface;
use Frankie\SizeParser\SizeParser;
use InvalidArgumentException;

class File implements FileInterface
{
    protected string $baseName;
    protected ?string $fileName;
    protected string $dirName;
    protected ?string $extension;
    protected int $size;

    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("File '$path' not exists.");
        }
        if (!is_file($path)) {
            throw new InvalidArgumentException("'$path' is a directory.");
        }
        $pathInfo = pathinfo($path);
        $this->dirName = $pathInfo['dirname'] ?? null;
        $this->baseName = $pathInfo['basename'];
        $this->extension = $pathInfo['extension'] ?? null;
        $this->fileName = $pathInfo['filename'] ?? null;
        $this->size = filesize($this->getFullPath());
    }

    public function getBaseName(): string
    {
        return $this->baseName;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getDirName(): string
    {
        return $this->dirName . DIRECTORY_SEPARATOR;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getFullPath(): string
    {
        return $this->dirName . DIRECTORY_SEPARATOR . $this->baseName;
    }

    public function getMimeType(): string
    {
        $fileInfo = new finfo();
        return $fileInfo->file($this->getFullPath(), FILEINFO_MIME_TYPE);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getParsedSize(SizeParser $sizeParser): string
    {
        return $sizeParser->setSize($this->size)
            ->parse()
            ->getParsed();
    }

    public function getContent(): string
    {
        return file_get_contents($this->getFullPath());
    }

    public function changeMode(int $mode): bool
    {
        return chmod($this->getFullPath(), $mode);
    }

    public function append(string $content): self
    {
        $fp = fopen($this->getFullPath(), 'ab');
        fwrite($fp, $content);
        fclose($fp);
        $this->size = filesize($this->getFullPath());
        return $this;
    }

    public function setContent(string $content): self
    {
        file_put_contents($this->getFullPath(), $content);
        $this->size = filesize($this->getFullPath());
        return $this;
    }

    public function prepend(string $content): self
    {
        $fileContents = file_get_contents($this->getFullPath(), $content);
        file_put_contents($this->getFullPath(), $content . $fileContents);
        $this->size = filesize($this->getFullPath());
        return $this;
    }

    /**
     * @return DateTime
     * @throws \Exception
     */
    public function getLastModifiedTime(): DateTime
    {
        return new DateTime(date('F d Y h:i A', filemtime($this->getFullPath())));
    }

    public function download(string $name = ''): ResponseInterface
    {
        if ($name === '') {
            $name = $this->baseName;
        }
        $response = new Response();
        $response->withStatus(200);
        $response->withHeader('Content-Description', 'File Transfer');
        $response->withHeader('Content-Type', 'application/octet-stream');
        $response->withHeader('Content-Length', (string)$this->size);
        $response->withHeader('Content-Transfer-Encoding', 'binary');
        $response->withHeader('Pragma', 'public');
        $response->withHeader('Expires', '0');
        $response->withHeader('Content-Disposition', 'attachment; filename="' . $name . '"');
        $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->withBody(file_get_contents($this->getFullPath()));
        return $response;
    }

    public function getMode(): string
    {
        return substr(sprintf('%o', fileperms($this->getFullPath())), -4);
    }
}
