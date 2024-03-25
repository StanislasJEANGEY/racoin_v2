<?php

namespace App\Controllers;

use model\Annonce;
use model\Photo;
use model\Annonceur;

class IndexController
{
    protected $annonce = array();

    public function displayAllAnnonce($twig, $menu, $chemin, $cat): void
    {
        $template = $twig->load("index.html.twig");
        $menu     = array(
            array(
                'href' => $chemin,
                'text' => 'Acceuil'
            ),
        );

        $this->getAll($chemin);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonce
        ));
    }

    public function getAll($chemin): void
    {
        $tmp     = Annonce::with("Annonceur")->orderBy('id_annonce', 'desc')->take(12)->get();
        $annonce = [];
        foreach ($tmp as $t) {
            $t->nb_photo = Photo::where("id_annonce", "=", $t->id_annonce)->count();
            if ($t->nb_photo > 0) {
                $t->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $t->id_annonce)
                    ->first()->url_photo;
            } else {
                $t->url_photo = '/img/noimg.png';
            }
            $t->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $t->id_annonceur)
                ->first()->nom_annonceur;
            $annonce[] = $t;
        }
        $this->annonce = $annonce;
    }
}
