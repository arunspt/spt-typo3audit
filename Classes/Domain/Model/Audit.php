<?php
namespace SPT\SptTypo3audit\Domain\Model;

/***
 *
 * This file is part of the "TYPO3 Audit" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Arun <arun@spawoz.com>, SpaowZ
 *
 ***/

/**
 * Audit
 */
class Audit extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * status
     *
     * @var string
     */
    protected $status = '';

    /**
     * version
     *
     * @var string
     */
    protected $version = '';

    /**
     * Returns the status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns the version
     *
     * @return string $version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the version
     *
     * @param string $version
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}
