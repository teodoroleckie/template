<?php

namespace Tleckie\Template\Compiler;

use RuntimeException;
use Tleckie\Template\Compiler\Parser\Rules;
use Tleckie\Template\Compiler\Parser\RulesInterface;
use function array_walk;
use function count;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;

/**
 * Class Compiler
 *
 * @package Tleckie\Template\Compiler
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Compiler implements CompilerInterface
{
    /** @var PathInterface */
    private PathInterface $templatePath;

    /** @var PathInterface */
    private PathInterface $compiledPath;

    /** @var RulesInterface[] */
    private array $rules;

    /**
     * Compiler constructor.
     *
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        if (!count($rules)) {
            $this->addRule(new Rules());
        }

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * @inheritdoc
     */
    public function addRule(RulesInterface $rule): CompilerInterface
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @return PathInterface
     */
    public function getTemplatePath(): PathInterface
    {
        return $this->templatePath;
    }

    /**
     * @inheritdoc
     */
    public function setTemplatePath(PathInterface $templatePath): CompilerInterface
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * @return PathInterface
     */
    public function getCompiledPath(): PathInterface
    {
        return $this->compiledPath;
    }

    /**
     * @inheritdoc
     */
    public function setCompiledPath(PathInterface $compiledPath): CompilerInterface
    {
        $this->compiledPath = $compiledPath;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function needsCompile(): bool
    {
        return !$this->compiledPath->fileExist() ||
            $this->templatePath->getModificationTime() >
            $this->compiledPath->getModificationTime();
    }

    /**
     * @inheritdoc
     */
    public function compile(): bool
    {
        if (!$this->templatePath->fileExist()) {
            throw new RuntimeException(
                sprintf(
                    'Template [%s] not found.',
                    $this->templatePath->getFullPath()
                )
            );
        }

        return $this->compiledPath->write(
            $this->compact($this->loadRules())
        );
    }

    /**
     * @param string $content
     * @return string
     */
    private function compact(string $content): string
    {
        return preg_replace('/\s{2,}/', ' ', $content);
    }

    /**
     * @return string
     */
    private function loadRules(): string
    {
        $compiler = clone $this;
        array_walk(
            $this->rules,
            static function (RulesInterface $rule) use (&$tags, $compiler) {
                $rule->setCompiler($compiler);

                foreach ($rule->rules() as $regex => $item) {
                    $tags[$regex] = $item;
                }
            }
        );

        $content = $this->templatePath->read();

        foreach ($tags as $preg => $callback) {
            $content = preg_replace_callback($preg, $callback, $content);
        }

        return $content;
    }
}
