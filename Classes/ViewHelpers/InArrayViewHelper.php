<?php
namespace Cylancer\Participants\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C.Gogolin <service@cylancer.net>
 *
 * @package Cylancer\Participants\ViewHelpers
 */
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
