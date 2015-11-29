<?php

namespace Web\AdminBundle\Services\Http;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

use Guzzle\Http\Client as GuzzleClient;
use RestClient\Client as RestClient;


class Client
{
	public $_clientDebug = false;
	
	public $_clientHost;
	
	public $_container;
	
	public $_kernel;
	
	public function __construct(ContainerInterface $serviceContainer, Kernel $kernel)
    {
        
        $this->_container = $serviceContainer;
        
        $this->_kernel = $kernel;

    }
	
	/**
	 * Make a client request
	 * @param string $uri
	 * @param string $object
	 * @throws \Exception
	 * @return object
	 */
	public function clientGetRequest($uri, $object = null, $debug = false)
	{
		$restParams = $this->_kernel->getContainer()->getParameter('rest');
		$serializer = $this->_container->get('jms_serializer');
		
		if(!$this->getClientHost())
			$this->setClientHost($restParams['client']);
		
		if($debug)
			$this->setClientdebug($debug);
		
		if($this->_clientDebug){
			echo "<pre>";
			var_dump($uri);
		}
			
			
			$client = new GuzzleClient($this->_clientHost, $restParams['option']);
			$request = $client->get($uri);
			try {
				$response = $request->send();
			} catch (Exception $e) {
				echo $response->getMessage();
			}
			if($this->_clientDebug){
				var_dump($response->getMessage());
				exit();
			}
			
			if($object=='boolean'){
				//var_dump($response->getBody()->__toString());
				//exit();
				if($response->getBody()->__toString()=='true')
					return true;
				return false;
			}
			
			if($object)
				$data = $serializer->deserialize($response->getBody(), $object, 'json');
			else
				return $response->getBody()->__toString();
			
			return $data;
		
		/*}else{
			//Debug
			$client = new RestClient($this->_clientHost);
			$resquest = $client->newRequest($uri);
			$response = $resquest->getResponse();
			var_dump($uri);
			echo $response->getParsedResponse();
			exit();
		}*/
	}
	
	public function clientPutRequest($uri, $data, $debug = false)
	{
		$restParams = $this->_kernel->getContainer()->getParameter('rest');
		$serializer = $this->_container->get('jms_serializer');
		
		if(!$this->getClientHost())
			$this->setClientHost($restParams['client']);
		
		if($debug)
			$this->setClientdebug($debug);
		
		if($this->_clientDebug){
			echo "<pre>";
			var_dump($uri);
		}
		
		//Serialize and send data
		$body = $serializer->serialize($data, 'json');
		$client = new GuzzleClient($this->_clientHost);
		$request = $client->put($uri, null, $body);
		
		try {
			$response = $request->send();
		} catch (Exception $e) {
			echo $response->getMessage();
		}
		
		if($this->_clientDebug){
			//var_dump($response->getBody());
			var_dump($response->getMessage());
			exit();
		}
		
		//Return reponse
		if(is_array($data))
			return $data;
		if(get_class($data))
			return  $serializer->deserialize($response->getBody(), get_class($data), 'json');
		return true;
	}
	
	public function getClienthost() {
		return $this->_clientHost;
	}
	public function setClienthost($_clientHost) {
		$this->_clientHost = $_clientHost;
		return $this;
	}
	public function getClientdebug() {
		return $this->_clientDebug;
	}
	public function setClientdebug($_clientDebug) {
		$this->_clientDebug = $_clientDebug;
		return $this;
	}
	
	
}
