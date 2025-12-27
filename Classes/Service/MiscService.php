<?php
namespace Cylancer\Participants\Service;

use Cylancer\Participants\Domain\Repository\ContentElementRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

class MiscService implements SingletonInterface
{


    public function __construct(
        private readonly ContentElementRepository $contentElementRepository
    ) {
    }

    public function getFlexformSettings(int $uid)
    {

        /** @var \Cylancer\Participants\Domain\Model\ContentElement $ce */
        $ce = $this->contentElementRepository->findByUid($uid);
        $return = [];
        foreach (GeneralUtility::xml2array($ce->getPiFlexform())['data'] as $cat) {
            foreach ($cat['lDEF'] as $key => $value) {
                $return[substr($key, 9)] = $value['vDEF'];
            }
        }

        $return['_pages'] = GeneralUtility::intExplode(',', $ce->getPages());

        return $return;
    }

    public static function toDateTime(int $year, int $month, int $day = 1): \DateTime
    {
        $return = new \DateTime();
        return $return
            ->setDate($year, $month, $day)
            ->setTime(0, 0, 0, 0);
    }

    public static function addMonths(\DateTime $date, int $offset = 1): \DateTime
    {
        $interval = new \DateInterval('P' . abs($offset) . 'M');
        return $offset < 0 ? $date->sub($interval) : $date->add($interval);
    }

    public static function addDays(\DateTime $date, int $offset = 1): \DateTime
    {
        $interval = new \DateInterval('P' . abs($offset) . 'D');
        return $offset < 0 ? $date->sub($interval) : $date->add($interval);
    }

    public static function addHours(\DateTime $date, int $offset = 1): \DateTime
    {
        $interval = new \DateInterval('PT' . abs($offset) . 'H');
        return $offset < 0 ? $date->sub($interval) : $date->add($interval);
    }

    public static function stringDateTimetoDateTime(string $dateTime): \DateTime
    {
        $parseResult = date_parse_from_format("Y-m-d H:i:s", $dateTime);
        if ($parseResult['error_count'] > 0) {
            throw new \Exception("Date format is invalid: " . $dateTime);
        }

        $return = new \DateTime();
        return $return
            ->setDate($parseResult['year'], $parseResult['month'], $parseResult['day'])
            ->setTime($parseResult['hour'], $parseResult['minute'], $parseResult['second'], 0);
    }



}