<?php

namespace Tleckie\Template\Tests\Compiler\Parser;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\Parser\Rules;
use Tleckie\Template\Compiler\Parser\RulesInterface;
use Tleckie\Template\Compiler\PathInterface;

/**
 * Class RulesTest
 *
 * @package Tleckie\Template\Tests\Compiler\Parser
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class RulesTest extends TestCase
{
    /** @var CompilerInterface|MockObject */
    private CompilerInterface|MockObject $compilerMock;

    /** @var RulesInterface */
    private RulesInterface $rules;

    /**
     * @test
     * @dataProvider rulesDataProvider
     * @param string $regex
     * @param string $content
     * @param string $expected
     */
    public function rules(string $regex, string $content, string $expected): void
    {
        $callback = $this->rules->rules()[$regex];

        static::assertEquals(
            $expected,
            preg_replace_callback($regex, $callback, $content)
        );
    }

    /**
     * @test
     */
    public function set(): void
    {
        static::assertInstanceOf(
            RulesInterface::class,
            $this->rules->setTemplatePath($this->createMock(PathInterface::class))
        );

        static::assertInstanceOf(
            RulesInterface::class,
            $this->rules->setCompiledPath($this->createMock(PathInterface::class))
        );
    }

    /**
     * @test
     */
    public function extends(): void
    {
        $templatePathMock = $this->createMock(PathInterface::class);

        $compiledPathMock = $this->createMock(PathInterface::class);

        $templatePathMock
            ->expects(static::once())
            ->method('withFile')
            ->with('List/Users.html')
            ->willReturn($templatePathMock);

        $compiledPathMock
            ->expects(static::once())
            ->method('withFile')
            ->with('List/Users.html.compiled')
            ->willReturn($compiledPathMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('getTemplatePath')
            ->willReturn($templatePathMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('getCompiledPath')
            ->willReturn($compiledPathMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('setTemplatePath')
            ->with($templatePathMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('setCompiledPath')
            ->with($compiledPathMock);

        $this->compilerMock
            ->expects(static::once())
            ->method('needsCompile')
            ->willReturn(true);

        $this->compilerMock
            ->expects(static::once())
            ->method('compile');

        $compiledPathMock
            ->expects(static::once())
            ->method('read')
            ->willReturn('Hello users!');

        $regex = '/\{\s?extends\s*([\w\-_\.\/]*)\}/Ui';

        $callback = $this->rules->rules()[$regex];

        static::assertEquals(
            'Hello users!',
            preg_replace_callback($regex, $callback, '{extends List/Users.html}')
        );
    }

    /**
     * @return array
     */
    public function rulesDataProvider(): array
    {
        return [
            ['/\{\#(.*)\#\}/Ui', '{# test #}', '<?php /* test */ ?>'],
            ['/\{set\s+(.*)\s*;?\s*\}/Ui', '{set $a = "25" }', '<?php $a = "25"; ?>'],
            ['/\{(elseif)\s+(.*)\}/Ui', '{elseif $a === 25}', '<?php }elseif($a === 25){ ?>'],
            ['/\{dump\s+(.*)\s*;?\s*\}/Ui', '{dump $a}', '<?php var_dump($a); ?>'],
            ['/\{(foreach|if|for)\s+(.*)\}/Ui', '{foreach $items as $item}', '<?php foreach($items as $item){ ?>'],
            ['/\{\{\s?(.*)\s*;?\s?\}\}/Ui', '{{$items}}', '<?php echo $items; ?>'],
            ['/\{else\/?\}/Ui', '{else}', '<?php }else{ ?>'],
            ['/\{(endforeach|endif|endfor)\}/Ui', '{endfor}', '<?php } ?>'],
        ];
    }

    protected function setUp(): void
    {
        $this->compilerMock = $this->createMock(CompilerInterface::class);

        $this->rules = new Rules();

        $this->rules->setCompiler($this->compilerMock);
    }
}
