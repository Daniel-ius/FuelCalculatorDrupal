<?php

namespace Drupal\fuel_calculator\Service;

use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Calculation service.
 */
class CalculationService {
  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerInterface $logger;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;


  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new instance.
   *
   * @param \EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \AccountProxyInterface $current_user
   *   The current user service.
   * @param \RequestStack $request_stack
   *   The request stack service.
   *
   * @return void
   *   Returns void
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    AccountProxyInterface $current_user,
    RequestStack $request_stack,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('fuel_calculator');
    $this->currentUser = $current_user;
    $this->requestStack = $request_stack;
  }

  /**
   * Calculates the fuel consumption and cost based on provided parameters.
   *
   * @param float $distance
   *   The distance traveled (in kilometers).
   * @param float $efficiency
   *   The fuel efficiency (in liters per 100 kilometers).
   * @param float $price
   *   The price of fuel per liter.
   *
   * @return array
   *   An associative array with the following keys:
   *   - 'spent': The amount of fuel spent (in liters, formatted as a string).
   *   - 'cost': The cost of the fuel (formatted as a string).
   */
  public function calculateFuel(
    float $distance,
    float $efficiency,
    float $price,
  ): array {
    $spent = $distance * $efficiency / 100;
    $cost = $spent * $price;

    $request = $this->requestStack->getCurrentRequest();
    $client_ip = $request ? $request->getClientIp() : 'CLI';

    $this->logger->notice(
          'Fuel Calculator: IP: @ip, User: @user, Distance: @distance km,
             Consumption: @consumption L/100km, Price: @price, Fuel: @fuel L, Cost: @cost',
          [
            '@ip' => $client_ip,
            '@user' => $this->currentUser->getDisplayName(),
            '@distance' => number_format($distance, 1),
            '@consumption' => number_format($efficiency, 2),
            '@price' => number_format($price, 2),
            '@fuel' => number_format($spent, 2),
            '@cost' => number_format($cost, 2),
          ]
      );

    try {
      $calculation = $this->entityTypeManager->getStorage(
            'fuel_calculation'
        )->create(
            [
              'distance' => $distance,
              'efficiency' => $efficiency,
              'price' => $price,
              'fuel_spent' => $spent,
              'fuel_cost' => $cost,
              'user_id' => $this->currentUser->id(),
              'ip_address' => $client_ip,
            ]
        );
      $calculation->save();
    }
    catch (\Exception $e) {
      $this->logger->error(
            'Failed to save fuel calculation: @error',
            ['@error' => $e->getMessage()]
            );
    }
    return [
      'spent' => number_format($spent, 2),
      'cost' => number_format($cost, 2),
    ];
  }

}
