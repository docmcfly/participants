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
abstract class PresentState
{


    const UNKNOWN = -1;

    const PRESENT = 1;

    const NOT_PRESENT = 0;
}
    