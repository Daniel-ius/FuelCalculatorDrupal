<?php

namespace Drupal\fuel_calculator\Plugin\rest\resource;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;

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
class FuelCalculationResource extends ResourceBase
{
    protected EntityTypeManagerInterface $entityTypeManager;
    protected AccountProxyInterface $currentUser;

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

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ResourceBase|FuelCalculationResource|static
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
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
    public function get($id = null): ResourceResponse
    {
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
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
    public function post(array $data): ResourceResponse
    {
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
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
    public function patch($id, array $data): ResourceResponse
    {
        if (!$this->currentUser->hasPermission('access fuel calculator api')) {
            throw new BadRequestHttpException('Access denied');
        }

        $calc = $this->entityTypeManager->getStorage('fuel_calculation')->load($id);
        if (!$calc) {
            throw new NotFoundHttpException("Calculation not found");
        }

        $needs_recalc = false;
        foreach (['distance', 'efficiency', 'price'] as $field) {
            if (isset($data[$field])) {
                $value = (float) $data[$field];
                if ($field !== 'price' && $value <= 0) {
                    throw new BadRequestHttpException("$field must be positive");
                }
                $calc->set($field, $value);
                $needs_recalc = true;
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
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
    public function delete($id): ResourceResponse
    {
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

    private function serialize($calc): array
    {
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
