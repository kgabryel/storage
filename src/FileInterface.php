<?php

namespace Frankie\Storage;

use DateTime;
use Frankie\Response\ResponseInterface;
use Frankie\SizeParser\SizeParser;

interface FileInterface
{
    public function __construct(string $path);

    public function getBaseName(): string;

    public function getFileName(): ?string;

    public function getDirName(): string;

    public function getExtension(): ?string;

    public function getFullPath(): string;

    public function getMimeType(): string;

    public function getSize(): int;

    public function getParsedSize(SizeParser $sizeParser): string;

    public function getContent(): string;

    public function changeMode(int $mode): bool;

    public function append(string $content): self;

    public function setContent(string $content): self;

    public function prepend(string $content): self;

    public function getLastModifiedTime(): DateTime;

    public function download(string $name = ''): ResponseInterface;

    public function getMode(): string;
}
