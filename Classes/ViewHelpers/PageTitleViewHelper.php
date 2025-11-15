<?php
namespace Cylancer\Participants\ViewHelpers;

use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class PageTitleViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('parameter', 'string', 't3 link, e.g. t3://page?uid=12', true);
    }

    public function render(): string
    {
        $uri = $this->arguments['parameter'];

        if (str_starts_with($uri, 't3:')) {
            // UID aus dem Link extrahieren
            parse_str(parse_url($uri, PHP_URL_QUERY), $parts);
            $uid = (int) ($parts['uid'] ?? 0);
            if ($uid === 0) {
                return $uri;
            }
            $repo = GeneralUtility::makeInstance(PageRepository::class);
            return $repo->getPage($uid)['title'] ?? $uri;
        } else {
            return $uri;
        }
    }
}
