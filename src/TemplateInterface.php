<?php

namespace Tleckie\Template;

use RuntimeException;
use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\PathInterface;

/**
 * Interface TemplateInterface
 *
 * @package Tleckie\Template
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
interface TemplateInterface
{
    /**
     * @param string $template
     * @param array  $context
     * @return string
     * @throws RuntimeException
     */
    public function render(string $template, array $context = []): string;

    /**
     * @return bool
     */
    public function developmentMode(): bool;

    /**
     * @return CompilerInterface
     */
    public function compiler(): CompilerInterface;

    /**
     * @param PathInterface $path
     * @return TemplateInterface
     */
    public function setTemplatePath(PathInterface $path): TemplateInterface;

    /**
     * @param PathInterface $path
     * @return TemplateInterface
     */
    public function setCompiledPath(PathInterface $path): TemplateInterface;

    /**
     * @param string $alias
     * @param object $helper
     * @return TemplateInterface
     */
    public function registerHelper(string $alias, object $helper): TemplateInterface;

    /**
     * @return array
     */
    public function helpers(): array;

    /**
     * @param array $helpers
     * @return TemplateInterface
     */
    public function registerHelpers(array $helpers): TemplateInterface;

    /**
     * @param string $helperAlias
     * @return object
     */
    public function __get(string $helperAlias): object;

    /**
     * @return bool
     */
    public function flushCompiled(): bool;
}
