<?php

namespace Tleckie\Template\Compiler;

use RuntimeException;

/**
 * Interface PathInterface
 *
 * @package Tleckie\Template\Compiler
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
interface PathInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string|null
     */
    public function getFile(): ?string;

    /**
     * @param string $file
     * @return PathInterface
     */
    public function withFile(string $file): PathInterface;

    /**
     * @return string|null
     */
    public function read(): ?string;

    /**
     * @return bool
     */
    public function fileExist(): bool;

    /**
     * @return string
     */
    public function getFullPath(): string;

    /**
     * @param string $content
     * @return bool
     */
    public function write(string $content): bool;

    /**
     * @return bool
     */
    public function dirExists(): bool;

    /**
     * @throws RuntimeException
     */
    public function createPath(): void;

    /**
     * @param string|null $path
     * @return bool
     */
    public function deleteDir(string $path = null): bool;

    /**
     * @return int
     * @throws RuntimeException
     */
    public function getModificationTime(): int;
}
