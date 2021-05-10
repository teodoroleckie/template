<?php

namespace Tleckie\Template;

use RuntimeException;
use Tleckie\Template\Compiler\Compiler;
use Tleckie\Template\Compiler\CompilerInterface;
use Tleckie\Template\Compiler\Path;
use Tleckie\Template\Compiler\PathInterface;
use function extract;
use function ob_get_clean;
use function ob_start;

/**
 * Class Template
 *
 * @package Tleckie\Template
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Template implements TemplateInterface
{
    /** @var PathInterface */
    private PathInterface $templatePath;

    /** @var PathInterface */
    private PathInterface $compiledPath;

    /** @var CompilerInterface */
    private CompilerInterface $compiler;

    /** @var array */
    private array $helpers = [];

    /** @var bool */
    private bool $developmentMode;

    /**
     * Template constructor.
     *
     * @param string                 $templatePath
     * @param string                 $compilePath
     * @param CompilerInterface|null $compiler
     * @param bool                   $developmentMode
     */
    public function __construct(
        string $templatePath,
        string $compilePath,
        CompilerInterface $compiler = null,
        bool $developmentMode = false
    ) {
        $this->templatePath = new Path($templatePath);
        $this->compiledPath = new Path($compilePath);
        $this->compiler = $compiler ?? new Compiler();
        $this->developmentMode = $developmentMode;
    }

    /**
     * @inheritdoc
     */
    public function developmentMode(): bool
    {
        return $this->developmentMode;
    }

    /**
     * @inheritdoc
     */
    public function compiler(): CompilerInterface
    {
        return $this->compiler;
    }

    /**
     * @inheritdoc
     */
    public function setTemplatePath(PathInterface $path): TemplateInterface
    {
        $this->templatePath = $path;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCompiledPath(PathInterface $path): TemplateInterface
    {
        $this->compiledPath = $path;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function registerHelpers(array $helpers): TemplateInterface
    {
        foreach ($helpers as $alias => $helper) {
            $this->registerHelper($alias, $helper);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function registerHelper(string $alias, object $helper): TemplateInterface
    {
        $this->helpers[$alias] = $helper;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function helpers(): array
    {
        return $this->helpers;
    }

    /**
     * @inheritdoc
     */
    public function __get(string $helperAlias): object
    {
        if (!isset($this->helpers[$helperAlias])) {
            throw new RuntimeException(sprintf('Helper [%s] not found.', $helperAlias));
        }

        return $this->helpers[$helperAlias];
    }

    /**
     * @inheritdoc
     */
    public function render(string $template, array $context = []): string
    {
        $this->checkCompiler($template);

        ob_start();

        $this->managerTemplate($context);

        return ob_get_clean();
    }

    /**
     * @param string $template
     * @throws RuntimeException
     */
    private function checkCompiler(string $template): void
    {
        $this->compiler
            ->setTemplatePath($this->templatePath->withFile($template))
            ->setCompiledPath($this->compiledPath->withFile(sprintf("%s.compiled", $template)));

        if (($this->developmentMode || $this->compiler->needsCompile()) && !$this->compiler->compile()) {
            throw new RuntimeException('Compiler failed');
        }
    }

    /**
     * @param array $context
     */
    private function managerTemplate(array $context): void
    {
        extract($context);

        include $this->compiler->getCompiledPath()->getFullPath();
    }

    /**
     * @inheritdoc
     */
    public function flushCompiled(): bool
    {
        return $this->compiledPath->deleteDir();
    }
}
