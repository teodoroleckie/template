<?php

namespace Tleckie\Template\Compiler\Parser;

use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\PathInterface;

/**
 * Class AbstractRules
 *
 * @package Tleckie\Template\Compiler\Parser
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
abstract class AbstractRules implements RulesInterface
{
    /** @var PathInterface */
    protected PathInterface $templatePath;

    /** @var PathInterface */
    protected PathInterface $compiledPath;

    /** @var CompilerInterface */
    protected CompilerInterface $compiler;

    /**
     * @inheritdoc
     */
    public function getCompiler(): CompilerInterface
    {
        return $this->compiler;
    }

    /**
     * @inheritdoc
     */
    public function setCompiler(CompilerInterface $compiler): RulesInterface
    {
        $this->compiler = $compiler;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTemplatePath(PathInterface $templatePath): RulesInterface
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCompiledPath(PathInterface $compiledPath): RulesInterface
    {
        $this->compiledPath = $compiledPath;

        return $this;
    }
}
