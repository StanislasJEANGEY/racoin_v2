<?php

namespace App\Controllers;

use App\Models\Annonce;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Views\Twig;

class HomeController extends DefaultController
{
    public function index(Request $request, ResponseInterface $response): ResponseInterface
    {
        $annonces = Annonce::with('annonceur')
            ->orderBy('id_annonce', 'desc')
            ->get();

        return $this->view->render($response, 'index.html.twig', ['annonces' => $annonces]);
    }

    public function getAnnonce(Request $request, ResponseInterface $response, $id): ResponseInterface
    {
        $annonce = Annonce::find($id)->first();

        if (!$annonce) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'item.html.twig', ['annonce' => $annonce]);

    }
}
