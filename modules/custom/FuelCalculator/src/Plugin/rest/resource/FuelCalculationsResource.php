<?php

namespace Drupal\fuel_calculator\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a REST resource for fuel calculations listing and creation.
 *
 * @RestResource(
 *   id = "fuel_calculation",
 *   label = @Translation("Fuel Calculations"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/fuel-calculations",
 *     "create" = "/api/v1/fuel-calculations"
 *   },
 *   methods = {"GET", "POST"}
 * )
 */
class FuelCalculationsResource extends ResourceBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
    protected AccountProxyInterface $currentUser;

  /**
   * Constructs a FuelCalculationsResource object.
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
        AccountProxyInterface $current_user,
    ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
        $this->entityTypeManager = $entity_type_manager;
        $this->currentUser = $current_user;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): FuelCalculationsResource|ResourceBase|ContainerFactoryPluginInterface|static
    {
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
   * @return \Drupal\rest\ResourceResponse
   *   The response containing fuel calculations.
   */
    public function get(): ResourceResponse
    {
        if (!$this->currentUser->hasPermission('access fuel calculator api')) {
            throw new BadRequestHttpException('You do not have permission to access this resource.');
        }

        try {
            $storage = $this->entityTypeManager->getStorage('fuel_calculation');
            $calculations = $storage->loadMultiple();

            $data = [];
            foreach ($calculations as $calculation) {
                $data[] = [
                'id' => $calculation->id(),
                'uuid' => $calculation->uuid(),
                'distance' => (float) $calculation->getDistance(),
                'efficiency' => (float) $calculation->getEfficiency(),
                'price' => (float) $calculation->getPrice(),
                'fuel_spent' => (float) $calculation->getFuelSpent(),
                'fuel_cost' => (float) $calculation->getFuelCost(),
                'user_id' => $calculation->get('user_id')->target_id,
                'ip_address' => $calculation->getIpAddress(),
                'created' => $calculation->getCreatedTime(),
                ];
            }

            return new ResourceResponse($data);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching fuel calculations: @message', ['@message' => $e->getMessage()]);
            throw new BadRequestHttpException('Error fetching fuel calculations.');
        }
    }

  /**
   * Responds to POST requests.
   *
   * @param array $data
   *   The request payload.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The created fuel calculation response.
   */
    public function post(array $data): ResourceResponse
    {
        if (!$this->currentUser->hasPermission('create fuel calculation entities')) {
            throw new BadRequestHttpException('You do not have permission to create fuel calculations.');
        }

        $required_fields = ['distance', 'efficiency', 'price'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                throw new BadRequestHttpException("Missing required field: $field");
            }
        }

        try {
            $distance = (float) $data['distance'];
            $efficiency = (float) $data['efficiency'];
            $price = (float) $data['price'];

            if ($distance <= 0 || $efficiency <= 0 || $price < 0) {
                throw new BadRequestHttpException('
                Distance and efficiency must be positive, price must be non-negative.');
            }

            $fuel_spent = ($distance / 100) * $efficiency;
            $fuel_cost = $fuel_spent * $price;

            $calculation = $this->entityTypeManager->getStorage('fuel_calculation')->create([
            'distance' => $distance,
            'efficiency' => $efficiency,
            'price' => $price,
            'fuel_spent' => $fuel_spent,
            'fuel_cost' => $fuel_cost,
            'user_id' => $this->currentUser->id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $calculation->save();

            $response_data = [
            'id' => $calculation->id(),
            'uuid' => $calculation->uuid(),
            'distance' => (float) $calculation->getDistance(),
            'efficiency' => (float) $calculation->getEfficiency(),
            'price' => (float) $calculation->getPrice(),
            'fuel_spent' => (float) $calculation->getFuelSpent(),
            'fuel_cost' => (float) $calculation->getFuelCost(),
            'user_id' => $calculation->get('user_id')->target_id,
            'ip_address' => $calculation->getIpAddress(),
            'created' => $calculation->getCreatedTime(),
            ];

            return new ResourceResponse($response_data, 201);
        } catch (\Exception $e) {
            $this->logger->error('Error creating fuel calculation: @message', ['@message' => $e->getMessage()]);
            throw new BadRequestHttpException('Error creating fuel calculation: ' . $e->getMessage());
        }
    }
}
