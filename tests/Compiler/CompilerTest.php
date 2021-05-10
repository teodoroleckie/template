<?php

namespace Tleckie\Template\Tests\Compiler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tleckie\Template\Compiler\Compiler;
use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\Parser\AbstractRules;
use Tleckie\Template\Compiler\Parser\Rules;
use Tleckie\Template\Compiler\PathInterface;

/**
 * Class CompilerTest
 *
 * @package Tleckie\Template\Tests\Compiler
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class CompilerTest extends TestCase
{
    /** @var PathInterface|MockObject */
    private PathInterface|MockObject $compiledPathMock;

    /** @var PathInterface|MockObject */
    private PathInterface|MockObject $templatePathMock;

    /** @var CompilerInterface */
    private CompilerInterface $compiler;

    /**
     * @test
     */
    public function rules(): void
    {
        $rules = new Rules();
        $compiler = new Compiler([$rules]);
        static::assertCount(1, $compiler->rules());

        $compiler = new Compiler();
        static::assertCount(1, $compiler->rules());
    }

    /**
     * @test
     */
    public function compiledPath(): void
    {
        $this->compiler->setCompiledPath($this->compiledPathMock);
        static::assertInstanceOf(PathInterface::class, $this->compiler->getCompiledPath());
    }

    /**
     * @test
     */
    public function templatePath(): void
    {
        $this->compiler->setTemplatePath($this->templatePathMock);
        static::assertInstanceOf(PathInterface::class, $this->compiler->getTemplatePath());
    }

    /**
     * @test
     */
    public function needsCompile(): void
    {
        $this->compiler->setTemplatePath($this->templatePathMock);
        $this->compiler->setCompiledPath($this->compiledPathMock);

        $this->compiledPathMock->expects(static::once())
            ->method('fileExist')
            ->willReturn(false);

        static::assertTrue($this->compiler->needsCompile());
    }

    /**
     * @test
     * @dataProvider needCompilerDataProvider
     * @param int  $templateTime
     * @param int  $compiledTime
     * @param bool $expected
     */
    public function needsCompileFileModificationTime(int $templateTime, int $compiledTime, bool $expected): void
    {
        $this->compiler->setTemplatePath($this->templatePathMock);
        $this->compiler->setCompiledPath($this->compiledPathMock);

        $this->compiledPathMock->expects(static::once())
            ->method('fileExist')
            ->willReturn(true);

        $this->templatePathMock->expects(static::once())
            ->method('getModificationTime')
            ->willReturn($templateTime);

        $this->compiledPathMock->expects(static::once())
            ->method('getModificationTime')
            ->willReturn($compiledTime);

        static::assertEquals($expected, $this->compiler->needsCompile());
    }

    /**
     * @return array[]
     */
    public function needCompilerDataProvider(): array
    {
        return [
            [2, 1, true],
            [2, 2, false]
        ];
    }

    /**
     * @test
     */
    public function compileException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template [/template/Tpl.html] not found.');

        $this->templatePathMock->expects(static::once())
            ->method('fileExist')
            ->willReturn(false);

        $this->templatePathMock->expects(static::once())
            ->method('getFullPath')
            ->willReturn('/template/Tpl.html');

        $this->compiler->setTemplatePath($this->templatePathMock);
        $this->compiler->setCompiledPath($this->compiledPathMock);

        $this->compiler->compile();
    }

    /**
     * @test
     */
    public function compile(): void
    {
        $rule = new class() extends AbstractRules {
            public function rules(): array
            {
                return [
                    '/\{\#(.*)\#\}/Ui' => function ($matches) {
                        return '<?php /*' . $matches[1] . '*/ ?>';
                    }
                ];
            }
        };

        $compiler = new Compiler([$rule]);

        $this->templatePathMock->expects(static::once())
            ->method('fileExist')
            ->willReturn(true);

        $this->templatePathMock->expects(static::once())
            ->method('read')
            ->willReturn('{# test #}');

        $this->compiledPathMock->expects(static::once())
            ->method('write')
            ->with('<?php /* test */ ?>')
            ->willReturn(true);

        $compiler->setTemplatePath($this->templatePathMock);
        $compiler->setCompiledPath($this->compiledPathMock);

        $compiler->compile();

        foreach ($compiler->rules() as $rule) {
            static::assertNotEquals(spl_object_id($compiler), spl_object_id($rule->getCompiler()));
        }
    }

    protected function setUp(): void
    {
        $this->compiledPathMock = $this->createMock(PathInterface::class);
        $this->templatePathMock = $this->createMock(PathInterface::class);

        $this->compiler = new Compiler();
    }
}
