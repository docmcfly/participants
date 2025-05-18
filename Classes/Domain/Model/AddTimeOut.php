<?php
namespace Cylancer\Participants\Domain\Model;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 */
class AddTimeOut extends TimeOut
{

    protected bool $updateCommitments = true;

    public function getUpdateCommitments(): bool
    {
        return $this->updateCommitments;
    }

    public function setUpdateCommitments(bool $updateCommitments): void
    {
        $this->updateCommitments = $updateCommitments;

    }
}
