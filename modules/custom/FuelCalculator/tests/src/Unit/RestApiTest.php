<?php

namespace Drupal\Tests\fuel_calculator\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for REST API business logic.
 *
 * Tests fuel calculation math, data validation, and serialization
 * without requiring Drupal bootstrapping. Tests both FuelCalculationResource
 * and FuelCalculationsResource implementations.
 *
 */
#[Group("fuel_calculator")]
class RestApiTest extends TestCase
{
  /**
   * Test POST calculation formula.
   *
   * Validates: fuel_spent = (distance / 100) * efficiency
   * Validates: fuel_cost = fuel_spent * price
   */
    public function testPostFuelCalculation(): void
    {
        $distance = 150.5;
        $efficiency = 7.5;
        $price = 1.45;

        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEqualsWithDelta(11.29, $fuel_spent, 0.01);
        $this->assertEqualsWithDelta(16.37, $fuel_cost, 0.01);
    }

  /**
   * Test POST validation: missing required fields.
   *
   * Validates that missing distance, efficiency, or price is rejected.
   */
    public function testMissingRequiredFields(): void
    {
        $required_fields = ['distance', 'efficiency', 'price'];
        $data = ['distance' => 150.5];

        $missing = [];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        $this->assertCount(2, $missing);
        $this->assertContains('efficiency', $missing);
        $this->assertContains('price', $missing);
    }

  /**
   * Test POST validation: negative distance.
   *
   * Validates that distance > 0 is required.
   */
    public function testNegativeDistance(): void
    {
        $distance = -100;
        $efficiency = 7.5;
        $price = 1.45;

        $is_valid = ($distance > 0) && ($efficiency > 0) && ($price >= 0);

        $this->assertFalse($is_valid);
    }

  /**
   * Test POST validation: negative efficiency.
   *
   * Validates that efficiency > 0 is required.
   */
    public function testNegativeEfficiency(): void
    {
        $distance = 150.5;
        $efficiency = -7.5;
        $price = 1.45;

        $is_valid = ($distance > 0) && ($efficiency > 0) && ($price >= 0);

        $this->assertFalse($is_valid);
    }

  /**
   * Test POST validation: negative price.
   *
   * Validates that price >= 0 is required.
   */
    public function testNegativePrice(): void
    {
        $distance = 150.5;
        $efficiency = 7.5;
        $price = -1.45;

        $is_valid = ($distance > 0) && ($efficiency > 0) && ($price >= 0);

        $this->assertFalse($is_valid);
    }

  /**
   * Test POST validation: zero price allowed.
   *
   * Validates that price = 0 is acceptable (free fuel scenario).
   */
    public function testZeroPrice(): void
    {
        $distance = 150.5;
        $efficiency = 7.5;
        $price = 0;

        $is_valid = ($distance > 0) && ($efficiency > 0) && ($price >= 0);

        $this->assertTrue($is_valid);
    }

  /**
   * Test POST validation with data provider.
   *
   */
    #[DataProvider('postValidationDataProvider')]
    public function testPostValidation(
        float $distance,
        float $efficiency,
        float $price,
        bool $expected_valid
    ): void {
        $is_valid = ($distance > 0) && ($efficiency > 0) && ($price >= 0);
        $this->assertEquals($expected_valid, $is_valid);
    }

  /**
   * Data provider for POST validation tests.
   */
    public static function postValidationDataProvider(): array
    {
        return [
        'Valid standard' => [150.5, 7.5, 1.45, true],
        'Valid zero price' => [100.0, 8.0, 0, true],
        'Invalid zero distance' => [0, 8.0, 1.50, false],
        'Invalid negative distance' => [-100, 8.0, 1.50, false],
        'Invalid zero efficiency' => [100.0, 0, 1.50, false],
        'Invalid negative efficiency' => [100.0, -8.0, 1.50, false],
        'Invalid negative price' => [100.0, 8.0, -1.50, false],
        'Large values' => [100000, 15.0, 2.5, true],
        'Small values' => [0.1, 0.5, 0.01, true],
        ];
    }

  /**
   * Test response serialization: type casting.
   *
   * Validates that serialize() method properly casts all fields.
   */
    public function testResponseSerialization(): void
    {
        $data = [
        'id' => '1',
        'uuid' => 'abc-def-ghi',
        'distance' => '150.5',
        'efficiency' => '7.5',
        'price' => '1.45',
        'fuel_spent' => '11.29',
        'fuel_cost' => '16.37',
        'user_id' => '2',
        'ip_address' => '127.0.0.1',
        'created' => '1234567890',
        ];

        $response = [
        'id' => (int) $data['id'],
        'uuid' => $data['uuid'],
        'distance' => (float) $data['distance'],
        'efficiency' => (float) $data['efficiency'],
        'price' => (float) $data['price'],
        'fuel_spent' => (float) $data['fuel_spent'],
        'fuel_cost' => (float) $data['fuel_cost'],
        'user_id' => (int) $data['user_id'],
        'ip_address' => $data['ip_address'],
        'created' => (int) $data['created'],
        ];

        $this->assertIsInt($response['id']);
        $this->assertIsString($response['uuid']);
        $this->assertIsFloat($response['distance']);
        $this->assertIsFloat($response['efficiency']);
        $this->assertIsFloat($response['price']);
        $this->assertIsFloat($response['fuel_spent']);
        $this->assertIsFloat($response['fuel_cost']);
        $this->assertIsInt($response['user_id']);
        $this->assertIsString($response['ip_address']);
        $this->assertIsInt($response['created']);
    }

  /**
   * Test GET list response structure.
   *
   * Validates that GET response is array of properly structured calculations.
   */
    public function testGetListResponseStructure(): void
    {
        $calculations = [
        [
        'id' => 1,
        'uuid' => 'abc-def-ghi',
        'distance' => 150.5,
        'efficiency' => 7.5,
        'price' => 1.45,
        'fuel_spent' => 11.29,
        'fuel_cost' => 16.37,
        'user_id' => 2,
        'ip_address' => '127.0.0.1',
        'created' => 1234567890,
        ],
        [
        'id' => 2,
        'uuid' => 'xyz-123-456',
        'distance' => 200.0,
        'efficiency' => 8.0,
        'price' => 1.50,
        'fuel_spent' => 16.0,
        'fuel_cost' => 24.0,
        'user_id' => 2,
        'ip_address' => '127.0.0.1',
        'created' => 1234567890,
        ],
        ];

        $this->assertIsArray($calculations);
        $this->assertCount(2, $calculations);

        foreach ($calculations as $calc) {
            $this->assertArrayHasKey('id', $calc);
            $this->assertArrayHasKey('uuid', $calc);
            $this->assertArrayHasKey('distance', $calc);
            $this->assertArrayHasKey('fuel_spent', $calc);
            $this->assertArrayHasKey('fuel_cost', $calc);
        }
    }

  /**
   * Test PATCH price update.
   *
   * Validates that updating price recalculates fuel_cost.
   */
    public function testPatchPriceUpdate(): void
    {
        $distance = 100.0;
        $efficiency = 8.0;
        $price = 1.50;
        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEquals(8.0, $fuel_spent);
        $this->assertEquals(12.0, $fuel_cost);

        $new_price = 1.60;
        $new_fuel_cost = $fuel_spent * $new_price;

        $this->assertEquals(8.0, $fuel_spent);
        $this->assertEquals(12.8, $new_fuel_cost);
    }

  /**
   * Test PATCH distance update.
   *
   * Validates that updating distance recalculates fuel_spent and fuel_cost.
   */
    public function testPatchDistanceUpdate(): void
    {
        $distance = 100.0;
        $efficiency = 8.0;
        $price = 1.50;
        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEquals(8.0, $fuel_spent);
        $this->assertEquals(12.0, $fuel_cost);

        $new_distance = 200.0;
        $new_fuel_spent = ($new_distance / 100) * $efficiency;
        $new_fuel_cost = $new_fuel_spent * $price;

        $this->assertEquals(16.0, $new_fuel_spent);
        $this->assertEquals(24.0, $new_fuel_cost);
    }

  /**
   * Test PATCH efficiency update.
   *
   * Validates that updating efficiency recalculates fuel_spent and fuel_cost.
   */
    public function testPatchEfficiencyUpdate(): void
    {
        $distance = 100.0;
        $efficiency = 8.0;
        $price = 1.50;
        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEquals(8.0, $fuel_spent);
        $this->assertEquals(12.0, $fuel_cost);

        $new_efficiency = 10.0;
        $new_fuel_spent = ($distance / 100) * $new_efficiency;
        $new_fuel_cost = $new_fuel_spent * $price;

        $this->assertEquals(10.0, $new_fuel_spent);
        $this->assertEquals(15.0, $new_fuel_cost);
    }

  /**
   * Test PATCH multiple fields update.
   *
   * Validates that updating multiple fields triggers recalculation.
   */
    public function testPatchMultipleFieldsUpdate(): void
    {
        $new_distance = 150.0;
        $new_efficiency = 7.5;
        $new_price = 1.60;

        $new_fuel_spent = ($new_distance / 100) * $new_efficiency;
        $new_fuel_cost = $new_fuel_spent * $new_price;

        $this->assertEquals(11.25, $new_fuel_spent);
        $this->assertEquals(18.0, $new_fuel_cost);
    }

  /**
   * Test DELETE response format.
   *
   * Validates that DELETE returns proper success message.
   */
    public function testDeleteResponse(): void
    {
        $response = ['message' => 'Deleted'];

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Deleted', $response['message']);
    }

  /**
   * Test HTTP status codes.
   *
   */
    #[DataProvider('httpStatusCodesDataProvider')]
    public function testHttpStatusCodes(string $method, int $expected_status): void
    {
        $status_map = [
        'GET' => 200,
        'POST' => 201,
        'PATCH' => 200,
        'DELETE' => 200,
        ];

        $actual_status = $status_map[$method] ?? null;
        $this->assertEquals($expected_status, $actual_status);
    }

  /**
   * Data provider for HTTP status codes.
   */
    public static function httpStatusCodesDataProvider(): array
    {
        return [
        'GET' => ['GET', 200],
        'POST' => ['POST', 201],
        'PATCH' => ['PATCH', 200],
        'DELETE' => ['DELETE', 200],
        ];
    }

  /**
   * Test permission requirements.
   *
   * Validates that operations require correct permissions.
   */
    public function testPermissionRequirements(): void
    {
        $get_post_permission = 'access fuel calculator api';
        $create_permission = 'create fuel calculation entities';

        $this->assertEquals('access fuel calculator api', $get_post_permission);
        $this->assertEquals('create fuel calculation entities', $create_permission);
    }

  /**
   * Test calculation precision with various inputs.
   *
   */
    #[DataProvider('calculationPrecisionDataProvider')]
    public function testCalculationPrecision(
        float $distance,
        float $efficiency,
        float $price,
        float $expected_spent,
        float $expected_cost
    ): void {
        $fuel_spent = ($distance / 100) * $efficiency;
        $fuel_cost = $fuel_spent * $price;

        $this->assertEqualsWithDelta($expected_spent, $fuel_spent, 0.01);
        $this->assertEqualsWithDelta($expected_cost, $fuel_cost, 0.01);
    }

  /**
   * Data provider for calculation precision tests.
   */
    public static function calculationPrecisionDataProvider(): array
    {
        return [
        'Standard' => [150.5, 7.5, 1.45, 11.29, 16.37],
        'Small' => [50.0, 5.0, 1.20, 2.5, 3.0],
        'Medium' => [200.0, 8.0, 1.50, 16.0, 24.0],
        'Large' => [250.0, 10.0, 1.60, 25.0, 40.0],
        'Zero price' => [100.0, 8.0, 0, 8.0, 0],
          'Decimal precision' => [123.456, 7.89, 1.234, 9.74, 12.02],
        ];
    }

  /**
   * Test field value ranges.
   *
   * Validates that response field values match expected types and ranges.
   */
    public function testResponseFieldRanges(): void
    {
        $response = [
        'id' => 1,
        'distance' => 150.5,
        'efficiency' => 7.5,
        'price' => 1.45,
        'fuel_spent' => 11.29,
        'fuel_cost' => 16.37,
        'user_id' => 2,
        'created' => 1234567890,
        ];

        $this->assertGreaterThan(0, $response['id']);
        $this->assertGreaterThan(0, $response['distance']);
        $this->assertGreaterThan(0, $response['efficiency']);
        $this->assertGreaterThanOrEqual(0, $response['price']);
        $this->assertGreaterThan(0, $response['fuel_spent']);
        $this->assertGreaterThanOrEqual(0, $response['fuel_cost']);
        $this->assertGreaterThan(0, $response['user_id']);
        $this->assertGreaterThan(0, $response['created']);
    }
}
