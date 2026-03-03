<?php

namespace Drupal\fuel_calculator\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for managing the Fuel Calculator.
 *
 * Provides functionality to build and display the Fuel Calculator form.
 */
class FuelCalculatorController extends ControllerBase
{
  /**
   * Builds and returns the Fuel Calculator form.
   *
   * @return array
   *   A render array representing the Fuel Calculator form.
   */
    public function content(): array
    {
        return \Drupal::formBuilder()
        ->getForm('Drupal\fuel_calculator\Form\FuelCalculatorForm');
    }
}
