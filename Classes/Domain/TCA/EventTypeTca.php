<?php
namespace Cylancer\Participants\Domain\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 * 
 */
class EventTypeTca
{

    public function computeTitleDescription(&$parameters)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        if ($record != null) {
            $description = isset($record['description']) ? strip_tags($record['description']) : '';
            if (strlen($description) > 24) {
                $description = substr($description, 0, 24) . '…';
            }
            $parameters['title'] = $record['title'] . (trim($description) == '' ? '' : ' (' . $description . ')');
        }
    }
}