<?php

namespace App\Controllers;

use model\Annonce;
use model\Annonceur;
use model\Photo;

class AdvertiserViewController {
    public function __construct(){
    }

    private function getAnnonceData($n): array
    {
        $tmp = Annonce::where('id_annonceur','=',$n)->get();
        $annonces = [];
        foreach ($tmp as $a) {
            $a->nb_photo = Photo::where('id_annonce', '=', $a->id_annonce)->count();
            if($a->nb_photo>0){
                $a->url_photo = Photo::select('url_photo')
                    ->where('id_annonce', '=', $a->id_annonce)
                    ->first()->url_photo;
            }else{
                $a->url_photo = '/img/noimg.png';
            }
            $annonces[] = $a;
        }
        return $annonces;
    }

    function afficherAnnonceur($twig, $menu, $chemin, $n, $cat): void
    {
        $this->annonceur = Annonceur::find($n);
        if(!isset($this->annonceur)){
            echo "404";
            return;
        }

        $annonces = $this->getAnnonceData($n);

        $template = $twig->load("annonceur.html.twig");
        echo $template->render(array('nom' => $this->annonceur,
            "chemin" => $chemin,
            "annonces" => $annonces,
            "categories" => $cat));
    }
}