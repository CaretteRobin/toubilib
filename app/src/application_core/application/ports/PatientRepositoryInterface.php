<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\patient\Patient;

interface PatientRepositoryInterface
{
    public function findById(string $id): ?Patient;
}
