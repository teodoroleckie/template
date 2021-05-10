<?php

namespace Tleckie\Template\Compiler\Parser;

use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\PathInterface;

/**
 * Interface RulesInterface
 *
 * @package Tleckie\Template\Compiler\Parser
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
interface RulesInterface
{
    /**
     * @param CompilerInterface $compiler
     * @return RulesInterface
     */
    public function setCompiler(CompilerInterface $compiler): RulesInterface;

    /**
     * @return CompilerInterface
     */
    public function getCompiler(): CompilerInterface;

    /**
     * @param PathInterface $templatePath
     * @return RulesInterface
     */
    public function setTemplatePath(PathInterface $templatePath): RulesInterface;

    /**
     * @param PathInterface $compiledPath
     * @return RulesInterface
     */
    public function setCompiledPath(PathInterface $compiledPath): RulesInterface;

    /**
     * @return array
     */
    public function rules(): array;
}
