<?php

namespace Drupal\vaccination_centers_customization\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an Registration link block.
 *
 * @Block(
 *   id = "vaccination_centers_customization_registration",
 *   admin_label = @Translation("Registration link"),
 *   category = @Translation("Vaccination Centers Customization")
 * )
 */
class RegistrationLink extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouter;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RegistrationLink constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $routeMatch, AccountInterface $account, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouter = $routeMatch;
    $this->currentUser = $account;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(
      parent::getCacheContexts(),
      [
        'user',
        'url.path',
        'url.query_args',
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node:' . $this->currentRouter->getParameter('node')->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'registration-block',
      ],
    ];

    $currentNode = $this->currentRouter->getParameter('node');
    $usersStorage = $this->entityTypeManager->getStorage('user');
    $user = $usersStorage->load($this->currentUser->id());
    $registeredLocation = $user->get('field_registered_location')->count() ? $user->get('field_registered_location')->getValue()[0]['target_id'] : NULL;

    if ($registeredLocation) {
      $nodeStorage = $this->entityTypeManager->getStorage('node');
      $label = $nodeStorage->load($registeredLocation)->label();
      $build['wrapper']['registered_user'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Registered vaccination center: @center', ['@center' => $label]),
      ];
    }
    else {
      if ((int) $currentNode->get('field_available_slots')->getValue()[0]['value'] === 0) {
        $build['wrapper']['slot_full'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Slots Full, kindly check some other vaccine center.'),
        ];
      }
      else {
        $build['wrapper']['link'] = [
          '#title' => $this->t('Register!'),
          '#type' => 'link',
          '#url' => Url::fromRoute('vaccination_centers_customization.registration', ['node' => $currentNode->id()]),
          '#attributes' => [
            'class' => 'button button--primary use-ajax',
          ],
        ];
      }
    }
    return $build;
  }

}
