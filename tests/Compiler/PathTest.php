<?php

namespace Tleckie\Template\Tests\Compiler;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tleckie\Template\Compiler\Path;

/**
 * Class PathTest
 *
 * @package Tleckie\Template\Tests\Compiler
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class PathTest extends TestCase
{
    /** @var Path */
    private Path $path;

    /** @var vfsStreamDirectory */
    private vfsStreamDirectory $pathFile;

    public function setUp(): void
    {
        $this->pathFile = vfsStream::setup();

        $this->path = new Path($this->pathFile->url() . '/cache');
    }

    /**
     * @test
     */
    public function getPath(): void
    {
        static::assertEquals('vfs://root/cache/', $this->path->getPath());

        $this->path = new Path($this->pathFile->url() . '/cache/');

        static::assertEquals('vfs://root/cache/', $this->path->getPath());
    }

    /**
     * @test
     */
    public function withFile(): void
    {
        $path = $this->path->withFile('/Test.html.compiled');

        static::assertFalse($path->fileExist());

        static::assertInstanceOf(Path::class, $path);

        static::assertEquals('Test.html.compiled', $path->getFile());
    }

    /**
     * @test
     */
    public function createPath(): void
    {
        $path = $this->path->withFile('Test.html.compiled');

        static::assertFalse($path->dirExists());

        static::assertNotEquals(spl_object_id($path), spl_object_id($this->path));

        $path->createPath();

        static::assertTrue($path->dirExists());
    }

    /**
     * @test
     */
    public function createPathThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $path = $this->path->withFile('/path/Test.html.compiled');

        rmdir('vfs://root/cache/');
        chmod('vfs://root/', 0015);

        $path->createPath();
    }

    /**
     * @test
     */
    public function getModificationTime(): void
    {
        $path = $this->path->withFile('Test.html.compiled');

        static::assertTrue($path->write('test'));

        static::assertTrue(0 < $path->getModificationTime());

        static::assertEquals('test', $path->read());
    }

    /**
     * @test
     */
    public function writeError(): void
    {
        $this->pathFile = vfsStream::setup(
            'root',
            null,
            ['cache' => ['Test.html.compiled' => 'test']]
        );

        $this->path = new Path($this->pathFile->url() . '/cache');

        $path = $this->path->withFile('Test.html.compiled');

        chmod('vfs://root/cache/Test.html.compiled', 0055);

        static::assertFalse($path->write('test'));
    }

    /**
     * @test
     */
    public function readThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('File [vfs://root/cache/Test.html.compiled] not exists');

        $path = $this->path->withFile('Test.html.compiled');

        $path->read();
    }

    /**
     * @test
     */
    public function getModificationTimeThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('File [vfs://root/cache/Test.html.compiled] not exists');

        $path = $this->path->withFile('Test.html.compiled');

        $path->getModificationTime();
    }

    /**
     * @test
     */
    public function deleteDir(): void
    {
        $this->path->createPath();

        mkdir('vfs://root/cache/test/');

        foreach (range(0, 5) as $one) {
            file_put_contents(
                sprintf('vfs://root/cache/%s-cache.txt', $one),
                'content'
            );
        }

        static::assertFileExists(sprintf('vfs://root/cache/1-cache.txt'));

        $this->path->deleteDir();

        static::assertFileDoesNotExist(sprintf('vfs://root/cache/1-cache.txt'));

        static::assertDirectoryDoesNotExist(sprintf('vfs://root/cache/test/'));
    }
}
