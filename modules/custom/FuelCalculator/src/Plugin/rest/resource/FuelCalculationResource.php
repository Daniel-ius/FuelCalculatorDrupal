<?php

namespace Drupal\fuel_calculator\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a RESTful resource for fuel calculations.
 *
 * This class handles CRUD operations for fuel calculation entities. It includes
 * functionality for retrieving, creating, updating, and deleting fuel
 * calculation records, validating user permissions,
 * and serializing entity data for responses.
 *
 * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
 *      Entity type manager.
 * @param \Drupal\Core\Session\AccountProxyInterface $current_user
 *      Current user.
 *
 * @RestResource(
 *    id = "fuel_calculation",
 *    label = @Translation("Fuel Calculation"),
 *    uri_paths = {
 *      "canonical" = "/api/v1/fuel-calculations/{id}",
 *      "collection" = "/api/v1/fuel-calculations"
 *    }
 *  )
 */
class FuelCalculationResource extends ResourceBase {

  /**
   * Entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;
  /**
   * Current user.
   */
  protected AccountProxyInterface $currentUser;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Creates an instance of the resource.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return \Drupal\rest\Plugin\ResourceBase|\Drupal\fuel_calculator\Plugin\rest\resource\FuelCalculationResource|static
   *   An instance of the resource class.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ResourceBase|FuelCalculationResource|static {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->getParameter('serializer.formats'),
          $container->get('logger.factory')->get('fuel_calculator'),
          $container->get('entity_type.manager'),
          $container->get('current_user')
      );
  }

  /**
   * Retrieves a fuel calculation or a list of calculations.
   *
   * @param int|null $id
   *   The ID of the specific calculation to retrieve. If NULL, retrieves all
   *   calculations.
   *
   * @return \Symfony\Component\HttpFoundation\ResourceResponse
   *   A response containing the serialized fuel calculation(s).
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the user does not have the necessary permissions.
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown when a specific calculation ID is not found.
   */
  public function get($id = NULL): ResourceResponse {
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new BadRequestHttpException('Access denied');
    }

    if ($id) {
      $calc = $this->entityTypeManager->getStorage('fuel_calculation')->load($id);
      if (!$calc) {
        throw new NotFoundHttpException("Calculation not found");
      }
      return new ResourceResponse($this->serialize($calc));
    }

    $query = $this->entityTypeManager->getStorage('fuel_calculation')->getQuery();
    $ids = $query->execute();
    $data = [];
    foreach ($this->entityTypeManager->getStorage('fuel_calculation')->loadMultiple($ids) as $calc) {
      $data[] = $this->serialize($calc);
    }
    return new ResourceResponse($data);
  }

  /**
   * Processes a POST request to create a fuel calculation record.
   *
   * @param array $data
   *   An associative array containing:
   *   - distance: The distance driven (in kilometers).
   *   - efficiency: The fuel efficiency (in liters per 100 kilometers).
   *   - price: The price of fuel (per liter).
   *
   * @return \Symfony\Component\HttpFoundation\ResourceResponse
   *   A response containing the serialized fuel calculation record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown if the user does not have the required permissions,
   *   if required data fields are missing, or if any data contains invalid values.
   */
  public function post(array $data): ResourceResponse {
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new BadRequestHttpException('Access denied');
    }

    foreach (['distance', 'efficiency', 'price'] as $field) {
      if (!isset($data[$field])) {
        throw new BadRequestHttpException("Missing: $field");
      }
    }

    $distance = (float) $data['distance'];
    $efficiency = (float) $data['efficiency'];
    $price = (float) $data['price'];

    if ($distance <= 0 || $efficiency <= 0 || $price < 0) {
      throw new BadRequestHttpException('Invalid values');
    }

    $fuel_spent = ($distance / 100) * $efficiency;
    $fuel_cost = $fuel_spent * $price;

    $calc = $this->entityTypeManager->getStorage('fuel_calculation')->create([
      'distance' => $distance,
      'efficiency' => $efficiency,
      'price' => $price,
      'fuel_spent' => $fuel_spent,
      'fuel_cost' => $fuel_cost,
      'user_id' => $this->currentUser->id(),
      'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    ]);
    $calc->save();

    return new ResourceResponse($this->serialize($calc), 201);
  }

  /**
   * Updates an existing fuel calculation with new data.
   *
   * @param int $id
   *   The ID of the calculation to update.
   * @param array $data
   *   An associative array containing the fields to update:
   *   - distance: The new distance value (must be positive).
   *   - efficiency: The new efficiency value (must be positive).
   *   - price: The new price value.
   *
   * @return \Symfony\Component\HttpFoundation\ResourceResponse
   *   A response containing the updated serialized fuel calculation.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the user does not have the necessary permissions, the input
   *   data is invalid, or a field value is non-positive when required.
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown when the calculation with the specified ID is not found.
   */
  public function patch($id, array $data): ResourceResponse {
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new BadRequestHttpException('Access denied');
    }

    $calc = $this->entityTypeManager->getStorage('fuel_calculation')->load($id);
    if (!$calc) {
      throw new NotFoundHttpException("Calculation not found");
    }

    $needs_recalc = FALSE;
    foreach (['distance', 'efficiency', 'price'] as $field) {
      if (isset($data[$field])) {
        $value = (float) $data[$field];
        if ($field !== 'price' && $value <= 0) {
          throw new BadRequestHttpException("$field must be positive");
        }
        $calc->set($field, $value);
        $needs_recalc = TRUE;
      }
    }

    if ($needs_recalc) {
      $distance = (float) $calc->get('distance')->value;
      $efficiency = (float) $calc->get('efficiency')->value;
      $price = (float) $calc->get('price')->value;
      $calc->set('fuel_spent', ($distance / 100) * $efficiency);
      $calc->set('fuel_cost', (($distance / 100) * $efficiency) * $price);
    }

    $calc->save();
    return new ResourceResponse($this->serialize($calc));
  }

  /**
   * Deletes a specific fuel calculation.
   *
   * @param int $id
   *   The ID of the fuel calculation to delete.
   *
   * @return \Symfony\Component\HttpFoundation\ResourceResponse
   *   A response indicating the deletion status.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the user does not have the necessary permissions.
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown when the specified calculation ID does not exist.
   */
  public function delete($id): ResourceResponse {
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new BadRequestHttpException('Access denied');
    }

    $calc = $this->entityTypeManager->getStorage('fuel_calculation')->load($id);
    if (!$calc) {
      throw new NotFoundHttpException("Calculation not found");
    }

    $calc->delete();
    return new ResourceResponse(['message' => 'Deleted']);
  }

  /**
   * Serializes a fuel calculation entity into an associative array.
   *
   * @param object $calc
   *   The fuel calculation entity to be serialized.
   *
   * @return array
   *   An associative array containing the serialized data of the fuel calculation,
   *   including properties like ID, UUID, distance, efficiency, price, fuel spent,
   *   fuel cost, user ID, IP address, and creation timestamp.
   */
  private function serialize($calc): array {
    return [
      'id' => (int) $calc->id(),
      'uuid' => $calc->uuid(),
      'distance' => (float) $calc->get('distance')->value,
      'efficiency' => (float) $calc->get('efficiency')->value,
      'price' => (float) $calc->get('price')->value,
      'fuel_spent' => (float) $calc->get('fuel_spent')->value,
      'fuel_cost' => (float) $calc->get('fuel_cost')->value,
      'user_id' => (int) $calc->get('user_id')->target_id,
      'ip_address' => $calc->get('ip_address')->value,
      'created' => (int) $calc->get('created')->value,
    ];
  }

}
