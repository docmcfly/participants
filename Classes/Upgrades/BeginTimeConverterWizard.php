<?php
declare(strict_types=1);
namespace Cylancer\Participants\Upgrades;

use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class BeginTimeConverterWizard implements UpgradeWizardInterface
{

    /**
     * Return the speaking name of this wizard
     */
    public function getTitle(): string
    {
        return '[cylancer.net] begin time converter wizard';
    }

    /**
     * Return the description for this wizard
     */
    public function getDescription(): string
    {
        return '[cylancer.net] begin time converter wizard';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     */
    public function executeUpdate(): bool
    {
        /** @var QueryBuilder $source */
        $source = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_participants_domain_model_event ');
        $source->select('uid', 'time', 'date')->from('tx_participants_domain_model_event');

        $sourceStatement = $source->execute();

        /** @var QueryBuilder $target */
        $target = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_participants_domain_model_event');
        $target->update('tx_participants_domain_model_event');
        $dateTime  = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('GMT'));
        

        while ($row = $sourceStatement->fetchAssociative()) {
            $target->where($target->expr()->eq('uid', $row['uid']));
            $tmp = explode(':',$row['time']);
            $dateTime->setTimestamp(0);
            $dateTime->setTime(intval($tmp[0]), intval($tmp[1]), intval($tmp[2]));
            $target->set('begin_time', $dateTime->getTimestamp());
           
            $tmp = explode('-',$row['date']);
            $dateTime->setTimestamp(0);
            $dateTime->setDate(intval($tmp[0]), intval($tmp[1]), intval($tmp[2]));
            $target->set('begin_date',  $dateTime->getTimestamp());

            $target->execute();
        }
        return true;
    }

    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {

        try {
            /** @var QueryBuilder $source */
            $source = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_participants_domain_model_event');
            $source->select('uid')
                ->from('tx_participants_domain_model_event')
                ->setMaxResults(1)
                ->where(
                        $source->expr()->eq('begin_date', 0)
                );

            $sourceStatement = $source->execute();
            if ($sourceStatement->fetchAssociative() !== false) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        // Add your logic here
        return [];
    }

    public function getIdentifier(): string
    {
        return 'participants_beginTimeConverterWizard';
    }
}