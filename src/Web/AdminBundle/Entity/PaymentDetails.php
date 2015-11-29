<?php
namespace Web\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Web\AdminBundle\Model\PaymentDetails as BasePaymentDetails;
/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\CoreBundle\Entity\PaymentDetailsRepository")
 */
class PaymentDetails extends BasePaymentDetails
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var datetime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;
    
	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}
	
	public function __toString()
	{
		return $this->offsetGet('PAYMENTINFO_0_TRANSACTIONID');
	}
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PaymentDetails
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}