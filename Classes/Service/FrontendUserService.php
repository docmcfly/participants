<?php
namespace Cylancer\Participants\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Context\Context;
use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class FrontendUserService implements SingletonInterface
{

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly ConnectionPool $connectionPool
    ) {
    }

    public static function getUid(object $object): int
    {
        return $object->getUid();
    }

    public function getCurrentUser(): FrontendUser|false
    {
        if (!$this->isLogged()) {
            return false;
        }
        return $this->frontendUserRepository->findByUid($this->getCurrentUserUid());
    }

    public function getCurrentUserUid(): int
    {
        if (!$this->isLogged()) {
            return false;
        }
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'id');
    }

    public function isLogged(): bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $isPreview = ($context->hasAspect('frontend.preview') && $context->getPropertyFromAspect('frontend.preview', 'isPreview'));
        if ($isPreview) {
            return $this->frontendUserRepository->findByUid($context->getPropertyFromAspect('frontend.user', 'id')) !== null;
        } else {
            return $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        }
    }

    public function contains($userGroup, $feugid, &$loopProtect = array()): bool
    {
        if ($userGroup->getUid() == $feugid) {
            return true;
        } else {
            if (!in_array($userGroup->getUid(), $loopProtect)) {
                $loopProtect[] = $userGroup->getUid();
                foreach ($userGroup->getSubgroup() as $sg) {
                    if ($this->contains($sg, $feugid, $loopProtect)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    public function getUserSubGroups(FrontendUser $frontendUser): array
    {
        $return = [];
        foreach ($frontendUser->getUsergroup() as $ug) {
            $return = array_merge($return, $this->_getSubgroups($ug, $return));
        }
        return $return;
    }

    public function getSubGroups(FrontendUserGroup $frontendUserGroup): array
    {
        return $this->_getSubgroups($frontendUserGroup);
    }

    private function _getSubgroups(FrontendUserGroup $frontendUserGroup, array &$return = []): array
    {
        $return[] = $frontendUserGroup->getUid();
        foreach ($frontendUserGroup->getSubgroup() as $ug) {
            $uid = $ug->getUid();
            if (!in_array($uid, $return)) {
                $return = array_unique(array_merge($return, $this->_getSubgroups($ug, $return)));
            }
        }
        return $return;
    }

    public function getUserTopGroups(FrontendUser $frontendUser): array
    {
        $return = [];
        foreach ($frontendUser->getUsergroup() as $ug) {
            $return = array_merge($return, $this->_getTopGroups($ug->getUid(), $return));
        }
        return $return;
    }

    public function getTopGroups(FrontendUserGroup $userGroup): array
    {
        return $this->_getTopGroups($userGroup->getUid());
    }

    private function _getTopGroups(int $ug, array &$return = []): array
    {
        $return[] = $ug;
        $qb = $this->connectionPool->getQueryBuilderForTable('fe_groups');
        $s = $qb->select('fe_groups.uid')
            ->from('fe_groups')
            ->where($qb->expr()
                ->inSet('subgroup', $ug))
            ->executeQuery();
        while ($row = $s->fetchAllAssociative()) {
            $uid = intVal($row['uid']);
            if (!in_array($uid, $return)) {
                $return = array_unique(array_merge($return, $this->_getTopGroups($uid, $return)));
            }
        }
        return $return;
    }
}
