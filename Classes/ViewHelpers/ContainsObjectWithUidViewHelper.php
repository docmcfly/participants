<?php
namespace Cylancer\Participants\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class ContainsObjectWithUidViewHelper extends AbstractViewHelper
{

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'int|string', 'The object uid ', true);
        $this->registerArgument('objects', 'array', 'The array of objects', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): bool
    {
        if (isset($arguments['objects'])) {

            if ($arguments['objects'] instanceof ObjectStorage) {
                $arguments['objects'] = $arguments['objects']->toArray();
            }

            if (is_array($arguments['objects'])) {

                // debug($arguments);
                foreach ($arguments['objects'] as $obj) {
                    if ($obj->getUid() === intval($arguments['uid'])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
