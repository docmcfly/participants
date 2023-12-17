<?php 
namespace Cylancer\Participants\Service;
use Cylancer\Participants\Domain\PublicOption;
use Cylancer\Participants\Domain\Repository\EventRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * This file is part of the "TaskManagement" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 * 
 * @package Cylancer\Participants\Service
 */
class ReasonsForPreventionService
{
    
    public static function reasonsForPreventionAction(array $storageUids, \DateTime $from, \DateTime $until, string $visibility = 'ALL'): array
    {

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $eventRepository = GeneralUtility::makeInstance(EventRepository::class, $objectManager);

        $eventRepository->injectPersistenceManager($persistenceManager);
        $querySettings = $eventRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($storageUids);
        $eventRepository->setDefaultQuerySettings($querySettings);#

        return $eventRepository->findEventsAt( $from, $until, ReasonsForPreventionService::mapVisiblity($visibility));

    }


    private static function mapVisiblity($visibility): int
    {
        if (!isset($visibility)) {
            return PublicOption::PUBLIC;
        } else if (strtoupper($visibility) === 'ALL') {
            return PublicOption::ALL;
        } else if (strtoupper($visibility) === 'INTERNAL') {
            return PublicOption::INTERNAL;
        } else {
            return PublicOption::PUBLIC;
        }
    }


}
