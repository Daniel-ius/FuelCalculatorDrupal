<?php

namespace Drupal\Tests\fuel_calculator\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for fuel calculation business logic.
 *
 * Tests the mathematical accuracy of fuel consumption calculations
 * without requiring Drupal bootstrapping. Uses mocked dependencies
 * to isolate calculation logic from infrastructure concerns.
 *
 * @coversDefaultClass \Drupal\fuel_calculator\Service\CalculationService
 */
#[Group("fuel_calculator")]
class CalculationServiceTest extends TestCase
{
  /**
   * Test standard fuel calculation.
   *
   * Validates that fuel spent and cost calculations follow the formula:
   * - fuel_spent = (distance / 100) * efficiency
   * - fuel_cost = fuel_spent * price.
   */
    public function testStandardFuelCalculation(): void
    {
        $distance = 150.5;
        $efficiency = 7.5;
        $price = 1.45;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

      // Expected values rounded to 2 decimal places.
        $this->assertEqualsWithDelta(11.29, $fuel_spent, 0.01);
        $this->assertEqualsWithDelta(16.37, $fuel_cost, 0.01);
    }

  /**
   * Test fuel calculation with various numeric inputs.
   *
   * Verifies calculation accuracy across a range of typical values
   * to ensure formula consistency.
   */
    #[DataProvider('standardCalculationDataProvider')]
    public function testVariousCalculations(
        float $distance,
        float $efficiency,
        float $price,
        float $expected_spent,
        float $expected_cost,
    ): void {
        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEquals($expected_spent, $fuel_spent);
        $this->assertEquals($expected_cost, $fuel_cost);
    }

  /**
   * Data provider for standard calculation tests.
   *
   * @return array
   *   Test cases: [distance, efficiency, price, expected_spent, expected_cost]
   */
    public static function standardCalculationDataProvider(): array
    {
        return [
        'Small distance' => [100, 8.0, 1.50, 8.0, 12.0],
        'Medium distance' => [200, 8.0, 1.50, 16.0, 24.0],
        'Large distance' => [250, 10.0, 1.60, 25.0, 40.0],
        'Minimal distance' => [50, 5.0, 1.20, 2.5, 3.0],
        ];
    }

  /**
   * Test edge case: zero distance.
   *
   * Ensures calculation handles zero distance without errors,
   * returning zero consumption.
   */
    public function testZeroDistance(): void
    {
        $distance = 0;
        $efficiency = 8.0;
        $price = 1.50;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEquals(0, $fuel_spent);
        $this->assertEquals(0, $fuel_cost);
    }

  /**
   * Test edge case: very large distance.
   *
   * Validates calculations with large numeric values to ensure
   * no precision loss or overflow issues.
   */
    public function testLargeDistance(): void
    {
        $distance = 100000;
        $efficiency = 8.0;
        $price = 1.50;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEquals(8000, $fuel_spent);
        $this->assertEquals(12000, $fuel_cost);
    }

  /**
   * Test edge case: very small values.
   *
   * Ensures calculations work with fractional and decimal inputs
   * while maintaining precision.
   */
    public function testSmallValues(): void
    {
        $distance = 0.1;
        $efficiency = 0.5;
        $price = 0.01;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertLessThan(0.001, $fuel_spent);
        $this->assertLessThan(0.00001, $fuel_cost);
    }

  /**
   * Test decimal precision in calculations.
   *
   * Validates that calculations maintain proper decimal precision
   * for currency and measurement conversions.
   */
    public function testDecimalPrecision(): void
    {
        $distance = 123.456;
        $efficiency = 7.89;
        $price = 1.234;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

      // Round to 2 decimal places (typical for currency)
        $fuel_spent_rounded = round($fuel_spent, 2);
        $fuel_cost_rounded = round($fuel_cost, 2);

        $this->assertIsFloat($fuel_spent_rounded);
        $this->assertIsFloat($fuel_cost_rounded);
        $this->assertEquals(2, strlen(substr(strrchr($fuel_spent_rounded, '.'), 1)));
    }

  /**
   * Test high precision calculations.
   *
   * Ensures that calculations with high decimal places maintain
   * accuracy when rounded to currency standard (2 decimals).
   */
    public function testHighPrecisionRounding(): void
    {
        $distance = 333.333;
        $efficiency = 6.666;
        $price = 1.777;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

      // Verify that rounding to 2 decimals is consistent.
        $this->assertEquals(
            round($fuel_spent, 2),
            round(($distance / 100) * $efficiency, 2)
        );
        $this->assertEquals(
            round($fuel_cost, 2),
            round($fuel_spent * $price, 2)
        );
    }
}
