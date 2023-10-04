<?php
namespace Cylancer\Participants\Domain\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 C. Gogolin <service@cylancer.net>
 *
 * This class contains a tca configuration function.
 * 
 * @package Cylancer\Participants\Domain\TCA
 */
class EventTca
{

    public function computeTitle(&$parameters)
    {
        $typeTitle = '';
        $description = '';
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_participants_domain_model_event')->createQueryBuilder();
        $statement = $qb->select('tx_participants_domain_model_eventtype.title')
            ->addSelect('tx_participants_domain_model_eventtype.description')
            ->addSelect('tx_participants_domain_model_event.uid')
            ->from('tx_participants_domain_model_event')
            ->join('tx_participants_domain_model_event', 'tx_participants_domain_model_eventtype', 'tx_participants_domain_model_eventtype', $qb->expr()
                ->eq('tx_participants_domain_model_event.event_type', $qb->quoteIdentifier('tx_participants_domain_model_eventtype.uid')))
            ->where($qb->expr()
                ->eq('tx_participants_domain_model_event.uid', $qb->createNamedParameter(intval($parameters['row']['uid']))))
            ->execute();
        while ($row = $statement->fetchAssociative()) {
            $description = strip_tags($row['description']);
            if (strlen($description) > 10) {
                $description = substr($description, 0, 10) . '…';
            }
            $typeTitle = $row['title'] . ' - ' . $description;
        }
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        if ($record != null) {
            if($record['full_day']) {
                $parameters['title'] = $typeTitle . ' (' .self::formatDateUTC($record['begin_date'], 'd.m.Y') . ')';
            } else {
                $parameters['title'] = $typeTitle . ' (' .self::formatDateUTC(  $record['begin_date'] + $record['begin_time'] , 'd.m.Y H:i') . ')';
            }
            
        } else {
            $parameters['title'] = $typeTitle;
        }
    }


    private static function formatDateUTC(int $timestamp, string $format): string {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new \DateTimeZone('UTC'));
        return $date->format($format); 


    }


}