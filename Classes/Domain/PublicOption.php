<?php
namespace Cylancer\Participants\Domain;

/**
 * This file is part of the "Participants" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C.Gogolin <service@cylancer.net>
 */
abstract class PublicOption
{

    const ALL = -1;

    const INTERNAL = 0;

    const PUBLIC = 1;

    const INHERITED = 2;
}
    