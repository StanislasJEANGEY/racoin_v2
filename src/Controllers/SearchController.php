<?php

namespace controllers;

use model\Annonce;
use model\Categorie;

class SearchController {

    private function getMenu($chemin, $text): array
    {
        return array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/search",
                'text' => $text)
        );
    }

    private function renderTemplate($twig, $templateName, $chemin, $cat, $additionalData = []): void
    {
        $template = $twig->load($templateName);
        $data = array_merge(array("chemin" => $chemin, "categories" => $cat), $additionalData);
        echo $template->render($data);
    }

    function show($twig, $chemin, $cat): void
    {
        $menu = $this->getMenu($chemin, "Recherche");
        $this->renderTemplate($twig, "search.html.twig", $chemin, $cat, ["breadcrumb" => $menu]);
    }

    private function buildQuery($array): object
    {
        $query = Annonce::select();

        if( !empty(trim($array['motclef'])) ) {
            $query->where('description', 'like', '%'.$array['motclef'].'%');
        }

        if( !empty(trim($array['codepostal'])) ) {
            $query->where('ville', '=', $array['codepostal']);
        }

        if ( $array['categorie'] !== "Toutes catégories" && $array['categorie'] !== "-----") {
            $categ = Categorie::select('id_categorie')->where('id_categorie', '=', $array['categorie'])->first()->id_categorie;
            $query->where('id_categorie', '=', $categ);
        }

        if ( $array['prix-min'] !== "Min" && $array['prix-max'] !== "Max") {
            if($array['prix-max'] !== "nolimit") {
                $query->whereBetween('prix', array($array['prix-min'], $array['prix-max']));
            } else {
                $query->where('prix', '>=', $array['prix-min']);
            }
        } elseif ( $array['prix-max'] !== "Max" && $array['prix-max'] !== "nolimit") {
            $query->where('prix', '<=', $array['prix-max']);
        } elseif ( $array['prix-min'] !== "Min" ) {
            $query->where('prix', '>=', $array['prix-min']);
        }

        return $query;
    }

    function research($array, $twig, $chemin, $cat): void
    {
        $menu = $this->getMenu($chemin, "Résultats de la recherche");

        if( empty(trim($array['motclef'])) &&
            empty(trim($array['codepostal'])) &&
            (($array['categorie'] === "Toutes catégories" || $array['categorie'] === "-----")) &&
            ($array['prix-min'] === "Min") &&
            ( ($array['prix-max'] === "Max") || ($array['prix-max'] === "nolimit") ) ) {
            $annonce = Annonce::all();
        } else {
            $query = $this->buildQuery($array);
            $annonce = $query->get();
        }

        $this->renderTemplate($twig, "indexController.html.twig", $chemin, $cat, ["breadcrumb" => $menu, "annonces" => $annonce]);
    }
}