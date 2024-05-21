<?php
namespace Cylancer\Participants\Domain\Model;
use Cylancer\Participants\Domain\PresentState;

/**
* This file is part of the 'Participants' Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* ( c ) 2022 C. Gogolin <service@cylancer.net>
*/

class Commitment extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

    /**
    * @var int
    */
    protected $present = PresentState::UNKNOWN;

    /**
    * @var bool
    */
    protected $presentDefault = false;

    /**
    * Event
    *
    * @var Event
    */
    protected $event = null;

    /**
    * @var FrontendUser
    */
    protected $user = null;

    /**
    * __construct
    */

    public function __construct() {
        // Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
    * Initializes all ObjectStorage properties
    * Do not modify this method!
    * It will be rewritten on each save in the extension builder
    * You may modify the constructor of this class instead
    *
    * @return void
    */
    protected function initStorageObjects() {
    }

    /**
    * @return FrontendUser $user
    */

    public function getUser() {
        return $this->user;
    }

    /**
    * @param FrontendUser $user
    * @return void
    */

    public function setUser( FrontendUser $user ) {
        $this->user = $user;
    }

    /**
    * @return int $present
    */

    public function getPresent() {
        return $this->present;
    }

    /**
    * @param int $present
    * @return void
    */

    public function setPresent( $present ) {
        $this->present = $present;
    }

    /**
    * @return bool
    */

    public function isPresent() {
        return $this->present;
    }

    /**
    * @return bool $presentDefault
    */

    public function getPresentDefault() {
        return $this->presentDefault;
    }

    /**
    * @param bool $presentDefault
    * @return void
    */

    public function setPresentDefault( $presentDefault ) {
        $this->presentDefault = $presentDefault;
    }

    /**
    * @return bool
    */

    public function isPresentDefault() {
        return $this->presentDefault;
    }

    /**
    * @return Event
    */

    public function getEvent(): Event {
        return $this->event;
    }

    /**
    * @param Event $event
    * @return void
    */

    public function setEvent( $event ): void {
        $this->event = $event;
    }

    /**
    * @return bool
    */

    public function getIsNotChangable(): bool {
        return  $this->getEvent()
        ->getDateTime()
        ->getTimestamp() < time();
    }
}