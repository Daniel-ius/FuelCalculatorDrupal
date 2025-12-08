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
use Drupal\fuel_calculator\Entity\FuelCalculation;

/**
 * Provides a REST resource for Fuel Calculations.
 *
 * @RestResource(
 *   id = "fuel_calculation",
 *   label = @Translation("Fuel Calculation"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/fuel-calculations/{id}",
 *     "collection" = "/api/v1/fuel-calculations"
 *   }
 * )
 */
class FuelCalculationResource extends ResourceBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a FuelCalculationResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
   * Responds to GET requests.
   *
   * @param string|null $id
   *   The fuel calculation ID (optional). If not provided, returns collection.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing fuel calculation(s).
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function get($id = NULL) {
    // Check permission
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to access the Fuel Calculator API.');
    }

    // Get single fuel calculation
    if ($id) {
      $fuel_calculation = $this->entityTypeManager->getStorage('fuel_calculation')->load($id);
      if (!$fuel_calculation) {
        throw new NotFoundHttpException("Fuel calculation with ID $id not found.");
      }

      return new ResourceResponse($this->serializeFuelCalculation($fuel_calculation), 200);
    }

    // Get all fuel calculations
    $query = $this->entityTypeManager->getStorage('fuel_calculation')->getQuery();
    $query->accessCheck(TRUE);
    $ids = $query->execute();

    if (empty($ids)) {
      return new ResourceResponse([], 200);
    }

    $fuel_calculations = $this->entityTypeManager->getStorage('fuel_calculation')->loadMultiple($ids);
    $data = [];
    foreach ($fuel_calculations as $calculation) {
      $data[] = $this->serializeFuelCalculation($calculation);
    }

    return new ResourceResponse($data, 200);
  }

  /**
   * Responds to POST requests.
   *
   * @param array $data
   *   The request body containing fuel calculation data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the created fuel calculation.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function post(array $data) {
    // Check permission
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to access the Fuel Calculator API.');
    }

    // Validate required fields
    $required_fields = ['distance', 'efficiency', 'price'];
    foreach ($required_fields as $field) {
      if (!isset($data[$field])) {
        throw new BadRequestHttpException("Missing required field: $field");
      }
    }

    // Validate numeric values
    foreach ($required_fields as $field) {
      if (!is_numeric($data[$field])) {
        throw new BadRequestHttpException("Field '$field' must be numeric.");
      }
    }

    // Create new fuel calculation entity
    $fuel_calculation = FuelCalculation::create([
      'distance' => (float) $data['distance'],
      'efficiency' => (float) $data['efficiency'],
      'price' => (float) $data['price'],
      'fuel_spent' => (float) $data['distance'] / 100 * (float) $data['efficiency'],
      'fuel_cost' => ((float) $data['distance'] / 100 * (float) $data['efficiency']) * (float) $data['price'],
      'ip_address' => $this->getClientIp(),
    ]);

    // Save the entity
    $fuel_calculation->save();

    return new ResourceResponse($this->serializeFuelCalculation($fuel_calculation), 201);
  }

  /**
   * Responds to DELETE requests.
   *
   * @param string $id
   *   The fuel calculation ID to delete.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response indicating deletion success.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function delete($id) {
    // Check permission
    if (!$this->currentUser->hasPermission('access fuel calculator api')) {
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission to access the Fuel Calculator API.');
    }

    $fuel_calculation = $this->entityTypeManager->getStorage('fuel_calculation')->load($id);
    if (!$fuel_calculation) {
      throw new NotFoundHttpException("Fuel calculation with ID $id not found.");
    }

    $fuel_calculation->delete();

    return new ResourceResponse(['message' => "Fuel calculation $id deleted successfully."], 200);
  }

  /**
   * Serialize a FuelCalculation entity.
   *
   * @param \Drupal\fuel_calculator\Entity\FuelCalculation $fuel_calculation
   *   The fuel calculation entity.
   *
   * @return array
   *   The serialized fuel calculation data.
   */
  protected function serializeFuelCalculation(FuelCalculation $fuel_calculation) {
    return [
      'id' => $fuel_calculation->id(),
      'uuid' => $fuel_calculation->uuid(),
      'distance' => (float) $fuel_calculation->getDistance(),
      'efficiency' => (float) $fuel_calculation->getEfficiency(),
      'price' => (float) $fuel_calculation->getPrice(),
      'fuel_spent' => (float) $fuel_calculation->getFuelSpent(),
      'fuel_cost' => (float) $fuel_calculation->getFuelCost(),
      'user_id' => (int) $fuel_calculation->getOwnerId(),
      'ip_address' => $fuel_calculation->getIpAddress(),
      'created' => (int) $fuel_calculation->getCreatedTime(),
    ];
  }

  /**
   * Get the client IP address.
   *
   * @return string
   *   The client IP address.
   */
  protected function getClientIp() {
    $request = \Drupal::request();
    $ip = $request->getClientIp();
    return $ip ?: '0.0.0.0';
  }

}
