<?php

namespace App\Controller;

use App\TripCalculate\TripCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TripController extends AbstractController
{
    #[Route('/trip', name: 'app_trip', methods: ['POST'])]
    public function calculatePrice(TripCalculator $calculator, array $data): JsonResponse
    {
        return $this->json($calculator->calculateCost($data));
    }
}
