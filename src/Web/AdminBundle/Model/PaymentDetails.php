<?php
namespace Web\AdminBundle\Model;

use Payum\Core\Model\ArrayObject;

class PaymentDetails extends ArrayObject
{
	protected $id;
	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
}