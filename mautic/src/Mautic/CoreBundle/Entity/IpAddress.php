<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class IpAddress
 * @ORM\Entity();
 * @ORM\Table(name="ip_addresses")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all")
 */
class IpAddress {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="ip_address", type="text", length=15)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     */
    private $ipAddress;

    /**
     * @ORM\Column(name="ip_details", type="text", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     */
    private $ipDetails;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return IpAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set ipDetails
     *
     * @param string $ipDetails
     * @return IpAddress
     */
    public function setIpDetails($ipDetails)
    {
        $this->ipDetails = $ipDetails;

        return $this;
    }

    /**
     * Get ipDetails
     *
     * @return string
     */
    public function getIpDetails()
    {
        return json_decode($this->ipDetails);
    }

    /**
     * Sets the Date/Time for new entities
     *
     * @ORM\PrePersist
     */
    public function onPrePersistSetIpDetails()
    {
        //@todo - configure other IP services
        $url = 'http://freegeoip.net/json/' . $this->getIpAddress();
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $data = @curl_exec($ch);
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen')) {
            $data = @file_get_contents($url);
        }

        if (!empty($data)) {
            $this->ipDetails = $data;
        }
    }
}