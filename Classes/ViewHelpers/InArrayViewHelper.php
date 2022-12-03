<?php
namespace Cylancer\Participants\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class InArrayViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('value', '*', 'The value ', true);
        $this->registerArgument('array', 'string,array', 'The array', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $a = is_array($arguments['array']) ? $arguments['array'] : explode(',', $arguments['array']);
        return in_array($arguments['value'], $a);
    }
}
