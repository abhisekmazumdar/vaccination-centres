<?php

namespace Drupal\vaccination_centers_customization\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Vaccination Customization routes.
 */
class VaccinationRegistrationController extends ControllerBase {

  /**
   * VaccinationRegistrationController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Builds the response.
   */
  public function build(NodeInterface $node) {

    $response = new AjaxResponse();

    $currentUser = $this->currentUser();
    $userStorage = $this->entityTypeManager->getStorage('user');
    $user = $userStorage->load($currentUser->id());

    $registeredLocation = $user->get('field_registered_location');
    $userCity = $user->get('field_city')->getValue()[0]['target_id'];

    $nodeCity = $node->get('field_city')->getValue()[0]['target_id'];
    $slots = $node->get('field_available_slots')->getValue()[0]['value'];

    if ($registeredLocation->count() == 0 && $slots > 0 && $userCity === $nodeCity) {
      $user->set('field_registered_location', $node);
      $user->save();
      $node->set('field_available_slots', --$slots);
      $node->save();
      $response->addCommand(new ReplaceCommand(".field--name-field-available-slots > .field__item", (string) $slots));
      $response->addCommand(new ReplaceCommand("#registration-block", "Registered vaccination center: " . $node->label()));
    }
    elseif ($userCity !== $nodeCity) {
      $content['#markup'] = "This is not your Current City's Vaccination Center.";
      $content['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $response->addCommand(new OpenModalDialogCommand('Registration Unsuccessful!!', $content));
    }
    return $response;
  }

}
