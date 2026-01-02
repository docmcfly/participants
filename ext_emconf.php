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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Participants',
    'description' => 'This extension takes care of all aspects of participation.',
    'category' => 'plugin',
    'author' => 'C. Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '5.7.17',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'bootstrap_package' => '15.0.0-15.0.99',
            'usertools' => '4.0.0-4.2.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

