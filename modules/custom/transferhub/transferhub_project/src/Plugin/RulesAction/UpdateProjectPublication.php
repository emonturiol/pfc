<?php

/**
 * @file
 * Contains \Drupal\custom_pub\Plugin\RulesAction\SetCustomPublishingOption.
 */

namespace Drupal\transferhub_project\Plugin\RulesAction;

//use Drupal\custom_pub\CustomPublishingOptionInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an action to trigger a custom publishing option.
 *
 * @RulesAction(
 *   id = "rules_transferhub_project_update_publication",
 *   label = @Translation("Publish/unpublish project based on workflow status"),
 *   category = @Translation("Content"),
 *   context = {
 *    "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity")
 *     ),
 *   }
 * )
 */
class UpdateProjectPublication extends RulesActionBase {

    /**
     * Sets the custom publishing option on a given entity.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity to be saved.
     */
    protected function doExecute() { //EntityInterface $entity

        $node = \Drupal::routeMatch()->getParameter('node');

        if ($node) {

            $workflow = $node->get("field_workflow")->getValue();
            
            //var_dump($workflow); die;
            $state = $workflow[0]["value"];

            //Comunicate state change to DeviceHub
            $this->updatePublicationStatus($node, $state);
        }
    }
    
    private function updatePublicationStatus(&$node, $state)
    {
        switch ($state)
        {
            case  "project_workflow_waiting_for_assignment": { }
            case  "project_workflow_devices_allocated": { }
            case  "project_workflow_devices_received": { }
            case  "project_workflow_finished": {
                //publish content
                $node->setPublished(true);
                $message = t("published after changing to state: ");
                break;
            }
            case "project_workflow_creation": {}
            case "project_workflow_draft": {}
            case "project_workflow_rejected": {}
            case "project_workflow_cancelled": {}
            default:
            {
                //unpublish content
                $node->setPublished(false);
                $message = t("unpublished after changing to state: ");
                break;
            }
        }
        $node->save();

        //LOG
        \Drupal::logger("transferhub_project")->info("Node ". $node->id() . ": " . $message . $state);
    }
}
