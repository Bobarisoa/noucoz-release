<?php

namespace App\CoreBundle\Entity\Common;

use Doctrine\ORM\EntityRepository;


class AbstractRepository extends EntityRepository
{
	public $limit=null;

	public $offset=null;
	
	public function getAll($property){
		
		$res = array();
		$query	  = $this->createQueryBuilder('a');
		$entities = $query->getQuery()->getArrayResult();
		
		foreach($entities as $entity)
			$res[$entity[$property]] = $entity[$property];
		
		$res = array_filter($res);
		
		sort($res);

		return $res;
	}
	
	public function getLimit() {
		return $this->limit;
	}
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}
	public function getOffset() {
		return $this->offset;
	}
	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}
	
	
	
}
