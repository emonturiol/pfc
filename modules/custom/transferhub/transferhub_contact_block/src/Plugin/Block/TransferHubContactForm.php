<?php

namespace Drupal\transferhub_contact_block\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
/**
 * Provides my custom block.
 *
 * @Block(
 *   id = "transferhub_contact_form",
 *   admin_label = @Translation("TransferHub contact form"),
 * )
 */

class TransferHubContactForm extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {

        //todo imprimir block
        //$form = \Drupal::formBuilder()->getForm("contact_message_feedback_form");
        /*$default_form = \Drupal::config('contact.settings')->get('contact_message_feedback_form');
        $entity = \Drupal::entityManager()->getStorage('contact_form')->load($default_form);

        $message = \Drupal::entityManager()
            ->getStorage('contact_message')
            ->create(array('contact_form' => $entity->id(),
            ));

        $form = \Drupal::service('entity.form_builder')->getForm($message);*/

        return array('#markup' =>  $form);
    }

    protected function blockAccess(AccountInterface $account) {
        return AccessResult::allowedIfHasPermission($account, 'access content');
    }
}


?>