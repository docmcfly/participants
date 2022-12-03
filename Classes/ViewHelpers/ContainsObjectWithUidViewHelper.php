<?php
namespace Cylancer\Participants\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class ContainsObjectWithUidViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int|string', 'The object uid ', true);
        $this->registerArgument('objects', 'array', 'The array of objects', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
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
