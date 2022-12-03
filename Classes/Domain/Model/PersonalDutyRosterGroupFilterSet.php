<?php
namespace Cylancer\Participants\Domain\Model;



/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 */
class PersonalDutyRosterGroupFilterSet
{

    /**
     *
     * @var FrontendUserGroup $commitment
     */
    private $frontendUserGroup = null;

    /**
     *
     * @var bool $isVisible
     */
    private $visible = true;

    /**
     *
     * @param FrontendUserGroup $commitment
     * @param bool $isVisible
     */
    public function __construct(FrontendUserGroup $frontendUserGroup = null, bool $isVisible = null)
    {
        $this->frontendUserGroup = $frontendUserGroup;
        $this->visible = $isVisible == null ? false : $isVisible;
    }

    /**
     *
     * @return bool
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     *
     * @param bool $visible
     * @return void
     */
    public function setVisible($visible): void
    {
        $this->visible = $visible;
    }

    /**
     *
     * @return FrontendUserGroup
     */
    public function getFrontendUserGroup(): FrontendUserGroup
    {
        return $this->frontendUserGroup;
    }

    /**
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return void
     */
    public function setFrontendUserGroup($frontendUserGroup)
    {
        $this->frontendUserGroup = $frontendUserGroup;
    }
}
