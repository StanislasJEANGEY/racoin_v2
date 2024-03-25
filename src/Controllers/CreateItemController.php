<?php

namespace App\Controllers;

use model\Annonce;
use model\Annonceur;

class CreateItemController
{
    function addItemView($twig, $menu, $chemin, $cat, $dpt): void
    {
        $this->renderTemplate($twig, "add.html.twig", array(
            "breadcrumb"   => $menu,
            "chemin"       => $chemin,
            "categories"   => $cat,
            "departements" => $dpt
        ));
    }

    private function isEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function renderTemplate($twig, $templateName, $data): void
    {
        $template = $twig->load($templateName);
        echo $template->render($data);
    }

    private function validateFields($fields, $rules): array
    {
        $errors = array();

        foreach ($rules as $field => $rule) {
            if ($rule === 'required' && empty($fields[$field])) {
                $errors[$field] = 'This field is required';
            } elseif ($rule === 'email' && !$this->isEmail($fields[$field])) {
                $errors[$field] = 'Please enter a valid email address';
            } elseif ($rule === 'numeric' && !is_numeric($fields[$field])) {
                $errors[$field] = 'Please enter a numeric value';
            }
        }

        return $errors;
    }

    private function setProperties($object, $properties): void
    {
        foreach ($properties as $property => $value) {
            $object->$property = $value;
        }
    }

    function addNewItem($twig, $menu, $chemin, $allPostVars): void
    {
        date_default_timezone_set('Europe/Paris');

        $rules = array(
            'nom' => 'required',
            'email' => 'email',
            'phone' => 'numeric',
            'ville' => 'required',
            'departement' => 'numeric',
            'categorie' => 'numeric',
            'title' => 'required',
            'description' => 'required',
            'price' => 'numeric',
            'psw' => 'required',
            'confirm-psw' => 'required'
        );

        $errors = $this->validateFields($_POST, $rules);

        if ($_POST['psw'] !== $_POST['confirm-psw']) {
            $errors['password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $this->renderTemplate($twig, "add-error.html.twig", array(
                "breadcrumb" => $menu,
                "chemin"     => $chemin,
                "errors"     => $errors
            ));
        } else {
            $annonceur = new Annonceur();
            $annonce = new Annonce();

            $this->setProperties($annonceur, array(
                'email' => htmlentities($allPostVars['email']),
                'nom_annonceur' => htmlentities($allPostVars['nom']),
                'telephone' => htmlentities($allPostVars['phone'])
            ));

            $this->setProperties($annonce, array(
                'ville' => htmlentities($allPostVars['ville']),
                'id_departement' => $allPostVars['departement'],
                'prix' => htmlentities($allPostVars['price']),
                'mdp' => password_hash($allPostVars['psw'], PASSWORD_DEFAULT),
                'titre' => htmlentities($allPostVars['title']),
                'description' => htmlentities($allPostVars['description']),
                'id_categorie' => $allPostVars['categorie'],
                'date' => date('Y-m-d')
            ));

            $annonceur->save();
            $annonceur->annonce()->save($annonce);

            $this->renderTemplate($twig, "add-confirm.html.twig", array("breadcrumb" => $menu, "chemin" => $chemin));
        }
    }
}