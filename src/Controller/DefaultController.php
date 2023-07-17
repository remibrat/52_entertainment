<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/')]
final class DefaultController extends AbstractController
{

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['message' => 'Welcome to the Belote API']);
    }

    #[Route('trickWinnersNT', name: 'trick_winners_nt', methods: ['GET'])]
    public function trickWinnersNT(Request $request) : JsonResponse
    {
        $play = $request->query->get('play');
        $cards = explode('-', $play);
        $winners = [];

        for ($i = 0; $i < count($cards); $i += 4) {
            $trick = array_slice($cards, $i, 4);
            $winners[] = $this->getTrickWinner($trick);
        }

        return $this->json($winners);
    }

    #[Route('/trickWinners', name: 'trick_winners', methods: ['GET'])]
    public function trickWinners(Request $request) : JsonResponse
    {
        $play = $request->query->get('play');
        $trump = $request->query->get('trump');
        $cards = explode('-', $play);
        $winners = [];

        for ($i = 0; $i < count($cards); $i += 4) {
            $trick = array_slice($cards, $i, 4);
            $winners[] = $this->getTrickWinner($trick, $trump);
        }

        return $this->json($winners);
    }

    private function getTrickWinner(array $trick, string $trump=null): string
    {
        $refColor = substr($trick[0], -1);
        $highestCard = $trick[0];

        foreach ($trick as $card) {
            $color = substr($card, -1);
            $rank = substr($card, 0, -1);

            if (!in_array($color, ['H', 'D', 'S', 'C'])) {
                throw new \Exception('Invalid color ' . $color);
            }

            if ($trump && $color === $trump 
                && (substr($highestCard, -1) !== $trump || $this->compareCards($rank, substr($highestCard, 0, -1)) > 0)) {
                $highestCard = $card;
            } elseif ($color === $refColor 
                && substr($highestCard, -1) !== $trump
                && $this->compareCards($rank, substr($highestCard, 0, -1)) > 0) {
                $highestCard = $card;
            }
        }

        return $highestCard;
    }

    private function compareCards(string $rank1, string $rank2): int
    {
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', 'T', 'J', 'Q', 'K', 'A'];
        $index1 = array_search($rank1, $ranks);
        $index2 = array_search($rank2, $ranks);

        if ($index1 === false || $index2 === false) {
            throw new \Exception('Invalid rank');
        }

        return $index1 - $index2;
    }

}