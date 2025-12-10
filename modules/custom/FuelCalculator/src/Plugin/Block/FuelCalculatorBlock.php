<?php

namespace Drupal\fuel_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for the Fuel Calculator functionality.
 *
 * @Block(
 *   id = "fuel_calculator_block",
 *   admin_label = @Translation("Fuel Calculator Block")
 * )
 */
class FuelCalculatorBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return \Drupal::formBuilder()->getForm('Drupal\fuel_calculator\Form\FuelCalculatorForm');
  }

}
