<?php

namespace controllers;

use models\ApiKey;

class KeyGeneratorController {

    private function getMenu($chemin): array
    {
        return array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/search",
                'text' => "Recherche")
        );
    }

    private function renderTemplate($twig, $templateName, $chemin, $cat, $additionalData = []): void
    {
        $template = $twig->load($templateName);
        $menu = $this->getMenu($chemin);
        $data = array_merge(array("breadcrumb" => $menu, "chemin" => $chemin, "categories" => $cat), $additionalData);
        echo $template->render($data);
    }

    function show($twig, $menu, $chemin, $cat): void
    {
        $this->renderTemplate($twig, "key-generator.html.twig", $chemin, $cat);
    }

    function generateKey($twig, $menu, $chemin, $cat, $nom): void
    {
        $nospace_nom = str_replace(' ', '', $nom);

        if($nospace_nom === '') {
            $this->renderTemplate($twig, "key-generator-error.html.twig", $chemin, $cat);
        } else {
            // Génere clé unique de 13 caractères
            $key = uniqid();
            // Ajouter clé dans la base
            $apikey = new ApiKey();

            $apikey->id_apikey = $key;
            $apikey->name_key = htmlentities($nom);
            $apikey->save();

            $this->renderTemplate($twig, "key-generator-result.html.twig", $chemin, $cat, ["key" => $key]);
        }
    }
}