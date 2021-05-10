<?php

namespace Tleckie\Template\Compiler\Parser;

use function sprintf;

/**
 * Class Rules
 *
 * @package Tleckie\Template\Compiler\Parser
 * @author  Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class Rules extends AbstractRules
{
    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $compiler = $this->compiler;

        return [
            '/\{\#(.*)\#\}/Ui' => function ($matches) {
                return '<?php /*' . $matches[1] . '*/ ?>';
            },
            '/\{set\s+(.*)\s*;?\s*\}/Ui' => function ($matches) {
                return '<?php ' . $matches[1] . '; ?>';
            },
            '/\{(elseif)\s+(.*)\}/Ui' => function ($matches) {
                return '<?php }' . $matches[1] . '(' . $matches[2] . '){ ?>';
            },
            '/\{else\/?\}/Ui' => function () {
                return '<?php }else{ ?>';
            },
            '/\{dump\s+(.*)\s*;?\s*\}/Ui' => function ($matches) {
                return '<?php var_dump(' . $matches[1] . '); ?>';
            },
            '/\{(foreach|if|for)\s+(.*)\}/Ui' => static function ($matches) {
                return '<?php ' . $matches[1] . '(' . $matches[2] . '){ ?>';
            },
            '/\{(endforeach|endif|endfor)\}/Ui' => static function ($matches) {
                return '<?php } ?>';
            },
            '/\{\{\s?(.*)\s*;?\s?\}\}/Ui' => static function ($matches) {
                return '<?php echo ' . $matches[1] . '; ?>';
            },
            '/\{\s?extends\s*([\w\-_\.\/]*)\}/Ui' => static function ($matches) use ($compiler) {
                $templatePath = $compiler->getTemplatePath()->withFile($matches[1]);
                $compilerPath = $compiler->getCompiledPath()->withFile(sprintf('%s.compiled', $matches[1]));

                $compiler->setTemplatePath($templatePath);
                $compiler->setCompiledPath($compilerPath);

                if ($compiler->needsCompile()) {
                    $compiler->compile();
                }

                return sprintf('%s', $compilerPath->read());
            }
        ];
    }
}
