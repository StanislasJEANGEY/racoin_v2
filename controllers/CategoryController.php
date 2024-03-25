<?php

namespace controllers;

use models\Categorie;
use models\Annonce;
use models\Photo;
use models\Annonceur;

class CategoryController {

    protected $categories = array();

    public function getCategories() {
        return Categorie::orderBy('nom_categorie')->get()->toArray();
    }

    private function getAnnonceData($n = null) {
        $query = Annonce::with(['Annonceur', 'Photo'])
            ->orderBy('id_annonce','desc');

        if ($n !== null) {
            $query->where('id_categorie', "=", $n);
        }

        $annonces = $query->get();

        foreach($annonces as $annonce) {
            $annonce->nb_photo = $annonce->photos->count();
            $annonce->url_photo = $annonce->photos->first() ? $annonce->photos->first()->url_photo : '/img/noimg.png';
            $annonce->nom_annonceur = $annonce->annonceur->nom_annonceur;
        }

        return $annonces;
    }

    public function getCategorieContent($chemin, $n): void
    {
        $this->annonce = $this->getAnnonceData($n);
    }

    public function displayCategorie($twig, $menu, $chemin, $cat, $n): void
    {
        $template = $twig->load("indexController.html.twig");

        $menu = array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/cat/".$n,
                'text' => Categorie::find($n)->nom_categorie)
        );

        $this->getCategorieContent($chemin, $n);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "categories" => $cat,
            "annonces" => $this->annonce));
    }
}