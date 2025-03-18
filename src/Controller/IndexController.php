<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

final class IndexController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    public function index(Request $request): Response
    {
        // Récupérer le client depuis le cookie (par défaut : clienta)
        $client = $request->cookies->get('client_id', 'clienta');

        return $this->render('index/index.html.twig', [
            'client' => $client
        ]);
    }

    #[Route('/change-client', name: 'change_client', methods: ['POST'])]
    public function changeClient(Request $request): JsonResponse
    {
        $client = $request->request->get('client', 'clienta');

        $response = new JsonResponse(['message' => 'Client changé']);
        $response->headers->setCookie(new Cookie('client_id', $client, strtotime('+1 day')));

        return $response;
    }

    #[Route('/load-content', name: 'load_content', methods: ['POST'])]
    public function loadContent(Request $request): Response
    {
        $client = $request->cookies->get('client_id', 'clienta');
        $module = $request->request->get('module', 'cars');
        $script = $request->request->get('script', 'ajax');

        $templatePath = "customs/$client/modules/$module/$script.html.twig";

        if ($this->container->get('twig')->getLoader()->exists($templatePath)) {
            return $this->render($templatePath);
        }

        return new Response("<p>Fichier introuvable : $templatePath</p>", Response::HTTP_NOT_FOUND);
    }
}
