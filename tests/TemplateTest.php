<?php

namespace Tleckie\Template\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\PathInterface;
use Tleckie\Template\Template;
use Tleckie\Template\TemplateInterface;

/**
 * Class TemplateTest
 *
 * @package Tleckie\Template\Tests\Compiler\Parser
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class TemplateTest extends TestCase
{
    /** @var PathInterface|MockObject */
    private PathInterface|MockObject $templatePathMock;

    /** @var PathInterface|MockObject */
    private PathInterface|MockObject $compiledPathMock;

    /** @var CompilerInterface|MockObject */
    private CompilerInterface|MockObject $compilerMock;

    /** @var TemplateInterface */
    private TemplateInterface $template;

    /**
     * @test
     */
    public function construct(): void
    {
        static::assertFalse($this->template->developmentMode());

        static::assertInstanceOf(MockObject::class, $this->template->compiler());

        static::assertInstanceOf(
            TemplateInterface::class,
            $this->template->setTemplatePath($this->templatePathMock)
        );

        static::assertInstanceOf(
            TemplateInterface::class,
            $this->template->setCompiledPath($this->compiledPathMock)
        );
    }

    /**
     * @test
     */
    public function registerHelpers(): void
    {
        $instance = new class() {
        };

        static::assertInstanceOf(
            TemplateInterface::class,
            $this->template->registerHelpers(['alias' => $instance])
        );

        static::assertCount(1, $this->template->helpers());

        static::assertEquals($instance, $this->template->alias);
    }

    /**
     * @test
     */
    public function getHelperThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('Helper [alias] not found.');

        $this->template->alias;
    }

    /**
     * @test
     */
    public function checkCompilerThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('Compiler failed');

        $this->compilerMock
            ->expects(static::once())
            ->method('setTemplatePath')
            ->willReturn($this->compilerMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('setCompiledPath');

        $this->compilerMock
            ->expects(static::once())
            ->method('needsCompile')
            ->willReturn(true);

        $this->compilerMock
            ->expects(static::once())
            ->method('compile')
            ->willReturn(false);

        $this->template->render('test.html', []);
    }

    /**
     * @test
     */
    public function render(): void
    {
        $this->template = new Template(
            '/templates',
            '/compiled',
            $this->compilerMock,
        );

        $this->compilerMock
            ->expects(static::once())
            ->method('setTemplatePath')
            ->willReturn($this->compilerMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('setCompiledPath');

        $this->compilerMock
            ->expects(static::once())
            ->method('setCompiledPath');

        $this->compilerMock
            ->expects(static::once())
            ->method('getCompiledPath')
            ->willReturn($this->compiledPathMock);

        $this->compiledPathMock
            ->expects(static::once())
            ->method('getFullPath')
            ->willReturn('vfs://root/compiler/template.html.compiler');

        static::assertEquals('25', $this->template->render('test.html', ['a' => 25]));
    }

    /**
     * @test
     */
    public function flushCompiled(): void
    {
        static::assertFileExists('vfs://root/compiler/template.html.compiler');

        $this->template->setCompiledPath($this->compiledPathMock);

        $this->compiledPathMock
            ->expects(static::once())
            ->method('deleteDir')
            ->willReturn(true);

        static::assertTrue($this->template->flushCompiled());
    }

    protected function setUp(): void
    {
        $root = vfsStream::setup(
            'root',
            null,
            [
                'template' => [
                    'template.html' => 'template'
                ],
                'compiler' => [
                    'template.html.compiler' => '<?php echo $a;'
                ],
            ]
        );

        $this->templatePathMock = $this->createMock(PathInterface::class);

        $this->compiledPathMock = $this->createMock(PathInterface::class);

        $this->compilerMock = $this->createMock(CompilerInterface::class);

        $this->template = new Template(
            'vfs://root/template/',
            'vfs://root/compiled/',
            $this->compilerMock,
        );
    }
}
