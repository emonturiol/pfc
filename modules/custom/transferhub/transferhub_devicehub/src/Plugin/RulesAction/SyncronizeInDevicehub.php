<?php

/**
 * @file
 * Contains \Drupal\custom_pub\Plugin\RulesAction\SetCustomPublishingOption.
 */

namespace Drupal\transferhub_devicehub\Plugin\RulesAction;

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
 *   id = "rules_syncronize_in_devicehub",
 *   label = @Translation("Syncronize in DeviceHub"),
 *   category = @Translation("Content"),
 *   context = {
 *    "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity")
 *     ),
 *   }
 * )
 */
class SyncronizeInDevicehub extends RulesActionBase {

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

            //LOG
            //drupal_set_message("Project " . $node->id() . " changed to status " . $state); todo
            \Drupal::logger("transferhub_devicehub")->info("project state changed: ".$node->id(). " -> ". $state);
            
            //Comunicate state change to DeviceHub
            $this->comunicateStateChange($node, $state);
        }
    }
    
    private function comunicateStateChange($node, $state)
    {       
        $api = new \Drupal\transferhub_devicehub\transferhub_DeviceHubRestClient();

        //TODO fill data
        $content = array();

        switch ($state)
        {
            case  "project_workflow_rejected":{
                //Reject project
                $url= "events/projects/reject";
                break;
            }
            case  "project_workflow_waiting_for_assignment": {
                //Accept project
                $url = "events/projects/accept";
                break;
            }
            case  "project_workflow_cancelled": {
                //Cancel project
                $url = "events/projects/cancel";
                break;
            }
            case  "project_workflow_finished": {
                //Finish project
                $url = "events/projects/finish";
                break;
            }                
        }
        
        //if (isset($url))
        //    $api->call("events/projects/reject","POST",$content);
    }
}
