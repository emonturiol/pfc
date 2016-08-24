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
 *   id = "rules_transferhub_devicehub_syncronize_state",
 *   label = @Translation("Update project state in DeviceHub"),
 *   category = @Translation("Content"),
 *   context = {
 *    "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity")
 *     ),
 *   }
 * )
 */
class SyncronizeProjectState extends RulesActionBase {

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

        $base_url = "http://" . \Drupal::request()->getHost() . base_path();
        if (strpos(\Drupal::request()->getHost(), "localhost") !== false)
        {
            //todo
            $base_url = "http://www.reutilitza.cat/";
        }

        $project_url = $base_url . "node/".$node->id();

        $content["date"] = date("Y-d-m") . "T00:00:00";
        $content["description"] = t("State changed in Transferhub"); //todo
        $content["byUser"] = $base_url . "user/" . \Drupal::currentUser()->id();
        //$content["byUser"] = $base_url . "user/77" ; //todo treure
        $content["project"] = $project_url;
        $content["url"] = $project_url;

        switch ($state)
        {
            case  "project_workflow_rejected":{
                //Reject project
                $content["@type"] = "projects:Reject";
                $url= "events/projects/reject";
                break;
            }
            case  "project_workflow_waiting_for_assignment": {
                //Accept project
                $content["@type"] = "projects:Accept";
                $url = "events/projects/accept";
                break;
            }
            case  "project_workflow_cancelled": {
                //Cancel project
                $content["@type"] = "projects:Cancel";
                $url = "events/projects/cancel";
                break;
            }
            case  "project_workflow_finished": {
                //Finish project
                $content["@type"] = "projects:Finish";
                $url = "events/projects/finish";
                break;
            }                
        }
        
        if (isset($url))
        {
           $api->call($api->db . "/" . $url,"POST",$content);
        }
    }
}
