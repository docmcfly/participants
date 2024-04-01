<?php
namespace Cylancer\Participants\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
class IcsBlankLineEncoderViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('value', '*', 'The value ', true);
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs htmlspecialchars() function.
     *
     * @return string the altered string
     * @see http://www.php.net/manual/function.htmlspecialchars.php
     * @api
     */
    public function render()
    {
        $value = $this->arguments['value'];
        if ($value === null) {
            $value = $this->renderChildren();
        }
        return str_replace("\\n\\n", "\\n \\n", str_replace(["\r\n","\r","\n"], "\\n", strip_tags($value)));
    }

}
