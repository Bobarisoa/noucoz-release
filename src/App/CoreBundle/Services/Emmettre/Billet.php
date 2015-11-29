<?php

namespace App\CoreBundle\Services\Emmettre;

use Symfony\Component\Finder\Finder;

/**
 * CSV Parser tool
 *
 */
class Billet
{
	/**
	 *
	 * @var EntityManager
	 */
	protected $em;
	protected $tfox;
	protected $templating;
	
	private $path;
	private $fileName;
	private $id;
	
	public function __construct(EntityManager $entityManager, $tfox, $templating)
	{
		$this->em = $entityManager;
		$this->tfox = $tfox;
		$this->tfox = $templating;
	}
	
	public function set($path, $fileName)
	{
		$this->path 			= $path;
		$this->fileName 		= $fileName;
		return $this;
	}
	
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	public function savePdf()
	{
		
		$pnr = $this->em->getRepository('AppCoreBundle:Pnr')->find($this->id);
		if(!$pnr){
			return  \Exception('PNR not found');
		}
	
		//Trick to prevent cache size exceeded
		//set_time_limit(0);
		while(ob_get_level()) ob_end_clean();
		ob_implicit_flush(true);
		 
		/*
		 * Construct PDF with Mpdf service
		* Layout based on HTML twig template
		*/
		$service	= $this->tfox;
		$pdf = $service->getMpdf(array('', 'A4', 8, 'Helvetica', 10, 10, 15, 15, 9, 9, 'L'));
		$pdf->AliasNbPages('{NBPAGES}');
		$pdf->setTitle('billet-' . date('d-m-Y-H-i', time()) . '.pdf');
		$pdf->SetHeader('Billet');
		$pdf->SetFooter('{DATE j/m/Y}|{PAGENO}/{NBPAGES}');
		 

		$html = $this->templating->renderResponse('AppCoreBundle:Pnr:billetpdf.pdf.twig', array(
				'pnr'	   => $pnr
		));
		 
		$pdf->WriteHTML($html);
		 
		//$url = 'billet-'.$pnr->getNumero().'.pdf';
		//$pdf->Output($url, 'F');
		$pdf->Output();
	
		/*$this->get('session')->getFlashBag()->add(
		 'success',
				'Le billet a bien été enregistré !'
		);*/
	
		//return $this->redirect($this->generateUrl('pnr_emit', array('id' => $id)));
	
		//return $url;
	}
	
	
}