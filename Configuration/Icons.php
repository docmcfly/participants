<?php

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

$icons = [];
foreach (['commitmentSettings', 'dutyRoster', 'personalDutyRoster', 'taskForceOverview', 'timeOutManagement'] as $key) {
    $icons['participants-' . $key] = [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => "EXT:participants/Resources/Public/Icons/Plugins/{$key}.svg",
    ];

}
return $icons;
