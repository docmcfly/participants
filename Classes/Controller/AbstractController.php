<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Service\MiscService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 *
 * This file is part of the "participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class AbstractController extends ActionController
{

    public function __construct(
        private readonly MiscService $miscService,
        private readonly FrontendUserService $frontendUserService,
        private readonly SiteFinder $siteFinder,
        private readonly Context $context,
    ) {
    }


    protected function getFlexformSettings($ceUid): array
    {
        return \array_merge($this->settings, $this->miscService->getFlexformSettings($ceUid));
    }

    private function getPageId(): int
    {
        return $this->request->getAttribute('currentContentObject')->data['pid'];
    }

    protected function getLanguage(): string
    {
        return $this->siteFinder
            ->getSiteByPageId($this->getPageId())
            ->getLanguageById(
                $this->context->getPropertyFromAspect('language', 'id')
            )
            ->getLocale()
            ->getName();
    }

}