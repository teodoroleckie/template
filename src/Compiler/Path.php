<?php

namespace Tleckie\Template\Compiler;

use DirectoryIterator;
use RuntimeException;
use function array_pop;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function implode;
use function is_dir;
use function is_file;
use function mkdir;
use function rmdir;
use function sprintf;
use function trim;
use function unlink;

/**
 * Class Path
 *
 * @package Tleckie\Template\Compiler
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Path implements PathInterface
{
    /** @var string|null */
    public ?string $file = null;

    /** @var string */
    private string $path;

    /**
     * Path constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = sprintf("%s/", rtrim($path, '/'));
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return PathInterface
     */
    public function withFile(string $file): PathInterface
    {
        $object = clone $this;

        $object->file = sprintf("%s", trim($file, '/'));

        return $object;
    }

    /**
     * @return string|null
     */
    public function read(): ?string
    {
        if (!$this->fileExist()) {
            throw new RuntimeException(
                sprintf('File [%s] not exists', $this->getFullPath())
            );
        }

        return file_get_contents($this->getFullPath());
    }

    /**
     * @return bool
     */
    public function fileExist(): bool
    {
        return is_file($this->getFullPath());
    }

    /**
     * @return string
     */
    public function getFullPath(): string
    {
        return sprintf("%s%s", $this->path, $this->file);
    }

    /**
     * @param string $content
     * @return bool
     */
    public function write(string $content): bool
    {
        if (!$this->dirExists()) {
            $this->createPath();
        }

        return false !== @file_put_contents(
            $this->getFullPath(),
            $content
        );
    }

    /**
     * @return bool
     */
    public function dirExists(): bool
    {
        return is_dir($this->getRealPath());
    }

    /**
     * @return string
     */
    private function getRealPath(): string
    {
        $items = explode('/', $this->getFullPath());

        if (!is_null($this->file)) {
            array_pop($items);
        }

        return implode('/', $items);
    }

    /**
     * @throws RuntimeException
     */
    public function createPath(): void
    {
        if (!mkdir($concurrentDirectory = $this->getRealPath(), 0755, true) &&
            !is_dir($concurrentDirectory)) {
            throw new RuntimeException(
                sprintf(
                    'Directory "%s" was not created',
                    $concurrentDirectory
                )
            );
        }
    }

    /**
     * @param string|null $path
     * @return bool
     */
    public function deleteDir(string $path = null): bool
    {
        $iterator = new DirectoryIterator($path ?? $this->getRealPath());

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir() && $this->deleteDir($fileInfo->getPathname())) {
                rmdir($fileInfo->getPathname());
            }

            if ($fileInfo->isFile()) {
                unlink($fileInfo->getPathname());
            }
        }

        return true;
    }

    /**
     * @return int
     */
    public function getModificationTime(): int
    {
        if (!$this->fileExist()) {
            throw new RuntimeException(
                sprintf('File [%s] not exists', $this->getFullPath())
            );
        }

        return filemtime($this->getFullPath());
    }
}
