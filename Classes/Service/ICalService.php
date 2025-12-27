<?php
namespace Cylancer\Participants\Service;

use Cylancer\Participants\Domain\Model\Event;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class ICalService implements SingletonInterface
{

    private const ICAL_HEADER =
        'BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//SabreDAV//SabreDAV//EN
X-WR-CALNAME:test
X-APPLE-CALENDAR-COLOR:#499AA2
REFRESH-INTERVAL;VALUE=DURATION:PT4H
X-PUBLISHED-TTL:PT4H
BEGIN:VTIMEZONE
TZID:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE';

    private const ICAL_FOOTER = 'END:VCALENDAR';

    /**
     * @param Event[] $events
     * @return string
     */
    public function createICal(string $domain, array $events): string
    {
        $tmp = [];
        $tmp[] = ICalService::ICAL_HEADER;

        foreach ($events as $event) {
            $tmp[] = 'BEGIN:VEVENT';
            $tmp[] = 'CREATED:' . date('Ymd', $event->getCrdate()) . 'T' . date("His", $event->getCrdate()) . 'Z';
            $tmp[] = 'DTSTAMP:' . date("Ymd", $event->getTstamp()) . 'T' . date("His", $event->getTstamp()) . 'Z';
            $tmp[] = 'UID:dutyRoster-' . $event->getUid() . '@' . $domain;
            if ($event->getfullDay()) {
                $tmp[] = 'DTSTART;VALUE=DATE:' . $event->getDate()->format("Ymd");
                $tmp[] = 'DTEND;VALUE=DATE:' . $event->getDate()->modify('+1 day')->format("Ymd");

            } else {
                $tmp[] = 'DTSTART;TZID=Europe/Berlin:' . $event->getDate()->format("Ymd") . 'T' . $event->getTime()->format("His");
                $tmp[] = 'DTEND;TZID=Europe/Berlin:' . $event->getDate()->format("Ymd") . 'T' . $event->getTime()->modify('+' . $event->getDuration() . ' hours')->format("His");
            }

            $tmp[] = 'SUMMARY:🚒 ' . htmlspecialchars($event->getEventType()->getTitle());

            $description = 'DESCRIPTION:';
            $descriptionText = $event->getPublicVisibleDescription();
            if (trim($descriptionText) !== '') {
                $description .= $this->encodeIscBlanks($event->getDescription());
            }

            $ugs = [];
            foreach ($event->getVisiblePublicUsergroups() as $ug) {
                $ugs[] = $ug->getTitle();
            }
            if (trim($descriptionText) !== '' && count($ugs) > 0) {
                $description .= '\n\n';
            }
            $description .= implode(', ', $ugs);

            $tmp[] = $description;

            $tmp[] = 'END:VEVENT';
        }
        $tmp[] = ICalService::ICAL_FOOTER;
        return implode("\n", $tmp);

    }


    private function encodeIscBlanks(string $value): string
    {
        return str_replace("\\n\\n", "\\n \\n", str_replace(["\r\n", "\r", "\n"], "\\n", strip_tags($value)));

    }
}