<?php
declare(strict_types = 1);
namespace Cylancer\Participants\Domain\Model;

/**
 * This file is part of the "User Tools" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Clemens Gogolin <service@cylancer.net>
 */
class ValidationResults
{

    /**
     *
     * @var string
     */
    protected $infos = array();

    /**
     *
     * @var string
     */
    protected $errors = array();

    /**
     *
     * @return array of srings
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     *
     * @return array of srings
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     * @return bool
     */
    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    /**
     *
     * @return bool
     */
    public function getHasErrors()
    {
        return ! empty($this->errors);
    }

    /**
     *
     * @return array of srings
     */
    public function hasInfos()
    {
        return ! empty($this->infos);
    }

    /**
     *
     * @param String $errorKey
     * @param array $arguments
     */
    public function addInfo(String $infoKey, array $arguments = []): void
    {
        $keySplit = explode('.', $infoKey, 2);
        $this->infos['info.' . $infoKey]['arguments'] = $arguments;
        $this->infos['info.' . $infoKey]['id'] = count($keySplit) == 2 ? $keySplit[0] : $infoKey;
    }

    /**
     *
     * @param String $errorKey
     * @param array $arguments
     */
    public function addError(String $errorKey, array $arguments = []): void
    {
        $keySplit = explode('.', $errorKey, 2);
        $this->errors['error.' . $errorKey]['arguments'] = $arguments;
        $this->errors['error.' . $errorKey]['id'] = count($keySplit) == 2 ? $keySplit[0] : $errorKey;
    }

    /**
     *
     * @param
     *            array
     */
    public function addInfos(array $infos)
    {
        foreach ($infos as $info) {
            $this->addInfo($info);
        }
    }

    /**
     *
     * @param
     *            array
     */
    public function addErrors(array $errors)
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }
}
