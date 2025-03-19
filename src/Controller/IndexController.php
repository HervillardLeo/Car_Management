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
            'client' => $client,
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

    #[Route('/load-cars', name: 'load_cars', methods: ['POST'])]
    public function loadCars(Request $request): Response
    {
        $client = $request->cookies->get('client_id', 'clienta');

        // Charger les fichiers JSON
        $carsFile = $this->getParameter('kernel.project_dir') . "/data/cars.json";
        $garagesFile = $this->getParameter('kernel.project_dir') . "/data/garages.json";

        if (!file_exists($carsFile) || !file_exists($garagesFile)) {
            return new Response("<p>Fichier JSON introuvable.</p>", Response::HTTP_NOT_FOUND);
        }

        $carsData = json_decode(file_get_contents($carsFile), true);
        $garagesData = json_decode(file_get_contents($garagesFile), true);

        // Transformer la liste des garages en un tableau associatif basé sur l'ID
        $garages = [];
        foreach ($garagesData as $garage) {
            $garages[$garage['id']] = $garage; // Associe garageId à son contenu
        }

        // Filtrer les voitures pour ne garder que celles du client actif
        $filteredCars = array_filter($carsData, fn($car): bool => $car['customer'] === $client);

        foreach ($filteredCars as &$car) {
            $car['garage'] = $garages[$car['garageId']]['title'] ?? 'Garage inconnu';
            $car['year_formatted'] = date('Y', $car['year']);
        }

        if (empty($filteredCars)) {
            return new Response("<p>Aucune voiture trouvée pour ce client.</p>", Response::HTTP_NOT_FOUND);
        }

        return $this->render("customs/$client/modules/cars/list.html.twig", [
            'cars' => array_values($filteredCars),
        ]);
    }

    #[Route('/car-details/{id}', name: 'car_details', methods: ['GET'])]
    public function carDetails(int $id): Response
    {
        $carsFile = $this->getParameter('kernel.project_dir') . "/data/cars.json";
        if (!file_exists($carsFile)) {
            return new Response("<p>Fichier JSON introuvable.</p>", Response::HTTP_NOT_FOUND);
        }

        $carsData = json_decode(file_get_contents($carsFile), true);

        $car = null;
        foreach ($carsData as $c) {
            if ($c['id'] === $id) {
                $car = $c;
                break;
            }
        }
        if (empty($car)) {
            return new Response("<p>Voiture non trouvée.</p>", Response::HTTP_NOT_FOUND);
        }
        $garagesFile = $this->getParameter('kernel.project_dir') . "/data/garages.json";
        $garagesData = json_decode(file_get_contents($garagesFile), true);

        $garage = null;
        foreach ($garagesData as $g) {
            if ($g['id'] === $car["garageId"]) {
                $garage = $g;
                break;
            }
        }

        $car["garage"] = $garage["title"] ?? 'Garage inconnu';
        $car["garageAddress"] = $garage["address"] ?? 'Adresse inconnue';

        return $this->render("cars/edit.html.twig", [
            'car' => $car,
        ]);
    }

    #[Route('/load-garages', name: 'load_garages', methods: ['POST'])]
    public function loadGarages(Request $request): Response
    {
        $client = $request->cookies->get('client_id');

        if ($client !== 'clientb') {
            return new Response("<p>Vous n'avez pas accès a ce module.</p>", Response::HTTP_FORBIDDEN);
        }
        $garagesData = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/data/garages.json'), true);

        // Filtrer uniquement les garages du client B
        $garages = array_filter($garagesData, fn($garage) => $garage['customer'] === $client);

        if (empty($garages)) {
            return new Response("<p>Aucun garage trouvé pour ce client.</p>", Response::HTTP_NOT_FOUND);
        }

        return $this->render("customs/$client/modules/garages/list.html.twig", [
            'garages' => array_values($garages),
        ]);
    }

    #[Route('/garage-details/{id}', name: 'garage_details', methods: ['GET'])]
    public function garageDetail(int $id): Response
    {
        $garagesFile = $this->getParameter('kernel.project_dir') . "/data/garages.json";
        if (!file_exists($garagesFile)) {
            return new Response("<p>Fichier JSON introuvable.</p>", Response::HTTP_NOT_FOUND);
        }
        $garagesData = json_decode(file_get_contents($garagesFile), true);

        foreach ($garagesData as $g) {
            if ($g['id'] === $id) {
                $garage = $g;
                break;
            }
        }

        if (!$garage) {
            return $this->json(['error' => 'Garage non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return $this->render("garages/edit.html.twig", [
            'garage' => $garage,
        ]);
    }
}
