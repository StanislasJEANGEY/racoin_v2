<?php

namespace App\Controllers;

use model\Departement;

class DepartmentController {

    protected $departments = array();

    private function getDepartmentData() {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }

    public function getAllDepartments() {
        $this->departments = $this->getDepartmentData();
        return $this->departments;
    }
}