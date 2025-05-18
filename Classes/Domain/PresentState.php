<?php
namespace Cylancer\Participants\Domain;

/**
 * This file is part of the "Participants" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 */
abstract class PresentState
{


    public const UNKNOWN = -1;

    public const PRESENT = 1;

    public const NOT_PRESENT = 0;
}
