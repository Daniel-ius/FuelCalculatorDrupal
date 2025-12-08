<?php

namespace Drupal\fuel_calculator\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

class FuelCalculatorController extends ControllerBase
{
    public function content(): array
    {
        return Drupal::formBuilder()
        ->getForm('Drupal\fuel_calculator\Form\FuelCalculatorForm');
    }
}
