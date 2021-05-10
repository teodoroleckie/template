<?php

namespace Tleckie\Template\Compiler;

use RuntimeException;
use Tleckie\Template\Compiler\Parser\RulesInterface;

/**
 * Interface CompilerInterface
 *
 * @package Tleckie\Template\Compiler
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
interface CompilerInterface
{
    /**
     * @param RulesInterface $rule
     * @return CompilerInterface
     */
    public function addRule(RulesInterface $rule): CompilerInterface;

    /**
     * @return array
     */
    public function rules(): array;

    /**
     * @param PathInterface $templatePath
     * @return CompilerInterface
     */
    public function setTemplatePath(PathInterface $templatePath): CompilerInterface;

    /**
     * @return PathInterface
     */
    public function getTemplatePath(): PathInterface;

    /**
     * @return PathInterface
     */
    public function getCompiledPath(): PathInterface;

    /**
     * @param PathInterface $compiledPath
     * @return CompilerInterface
     */
    public function setCompiledPath(PathInterface $compiledPath): CompilerInterface;

    /**
     * @return bool
     */
    public function needsCompile(): bool;

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function compile(): bool;
}
