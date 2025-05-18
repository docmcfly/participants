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
abstract class PublicOption
{

    public const ALL = -1;

    public const INTERNAL = 0;

    public const PUBLIC = 1;

    public const INHERITED = 2;
}
