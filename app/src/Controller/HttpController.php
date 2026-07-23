<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HttpController extends AbstractController {

	#[Route( '/http/har', name: 'app_http_har' )]
	public function har(): Response {
		return $this->render(
			'http/har.html.twig',
			array(
				'controller_name' => 'HttpController',
				'title'           => 'HAR Viewer',
			)
		);
	}

	#[Route( '/http/cookie', name: 'app_http_cookie' )]
	public function cookie(): Response {
		return $this->render(
			'http/cookie.html.twig',
			array(
				'controller_name' => 'HttpController',
				'title'           => 'Cookie Encoder/Decoder',
			)
		);
	}
}
