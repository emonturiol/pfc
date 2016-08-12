<?php

namespace Drupal\transferhub_devicehub\Plugin\rest\resource;

//*   serialization_class = "Drupal\node\Entity\Node",
//*  serialization_class = "Drupal\serialization\Normalizer\NullNormalizer"
//*  serialization_class = "Symfony\Component\Serializer\Normalizer\ArrayDenormalizer"
//*     "https://www.drupal.org/link-relations/create" = "/transferhub/events/devices/allocate"

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use \Drupal\workflow\Entity\Workflow;
use \Drupal\workflow\Entity\WorkflowTransition;

/**
 * Something.
 *
 * @RestResource(
 *   id = "transferhub_allocate",
 *   label = @Translation("Allocate devices for a project"),
 *   uri_paths = {
 *     "canonical" = "/transferhub/events/devices/allocate",
 *     "https://www.drupal.org/link-relations/create" = "/transferhub/events/devices/allocate"
 *   },
 *  serialization_class = "Drupal\file\Entity\File",
 * )
 */
class TransferhubAllocateResource extends ResourceBase {


    public function _raiseError($message, $params = NULL, $status = 422)
    {
        $response = array();
        $response['status'] = '_error';
        if ($params)
            $response["error"] = t($message, $params);
        else
            $response["error"] = t($message);
        //todo LOG whatchdog
        \Drupal::logger("transferhub_devicehub")->error("REST SERVER: ERROR: ".$response["error"]." | status: ".$status);
        return new ResourceResponse($response, $status);
    }
    /**
     * Responds to POST requests.
     *
     * Returns a watchdog log entry for the specified ID.
     *
     *
     * @return \Drupal\rest\ResourceResponse
     *   The response containing the log entry.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *   Thrown when the log entry was not found.
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *   Thrown when no log entry was provided.
     */
    public function post($type, $request) {

        $data =  json_decode($request->getContent(), true);

        \Drupal::logger("transferhub_devicehub")->info("REST SERVER: Request: ".$request->getContent());

        //debug
        //$response["byUser"] = $data["byUser"];
        //$response["@type"] = $data["@type"];
        // debug*******************

        //VALIDATION
        /*
            400 Bad Request –
            422 Unprocessable Entity – Document fails validation.
            403 Forbidden –
            404 Not Found –
            405 Method Not Allowed –
            406 Not Acceptable –
            415 Unsupported Media Type –
            500 Internal Server Error – Any non-documented error. Please, report if you get this code.
            200 OK - (GET)
            201 Created – (POST)
         */

        //todo type ?
        //$response['status'] = 'ERROR';
        //$response["error_msg"] = t("@type field must be `Allocate`");

        //node
        $project_url = $data["project"];
        $url_array = explode("/",$project_url);
        $nid = $url_array[count($url_array)-1];
        $node = \Drupal\node\Entity\Node::load($nid);
        if (!$node || $node->getType() != "project")
        {
            return $this->_raiseError("Project with id @nid does not exist",array("@nid" => $nid),422);
        }
        //workflow state
        $current_state =  $node->get("field_workflow")->getValue()[0]["value"];
        if ($current_state != "project_workflow_waiting_for_assignment" && $current_state != "project_workflow_devices_allocated" && $current_state != "project_workflow_devices_received")
        {
            return $this->_raiseError("Cannot allocate devices because project is in state @state", array("@state" => $current_state), 422);
        }
        //devices
        if (!$data["devices"] || count($data["devices"]) == 0) {

            return $this->_raiseError("No devices sent", NULL , 422);
        }
        //set update date //TODO

        //set devices
        $node->field_allocated_devices = \Drupal\Core\Field\FieldItemList::createInstance(\Drupal\field_collection\Entity\FieldCollectionItem::class);

        foreach($data["devices"] as $device)
        {
            $did = $device["_id"];
            $dtype = $device["@type"];
            $dsubtype = $device["type"];
            $dmanufacturer = $device["manufacturer"];
            $dmodel = $device["model"];
            $durl = $device["url"];

            $fieldCollectionItem = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_allocated_devices']);
            $fieldCollectionItem->setHostEntity($node);
            $fieldCollectionItem->set('field_id', $did);
            $fieldCollectionItem->set('field_type', $dtype);
            $fieldCollectionItem->set('field_subtype', $dsubtype);
            $fieldCollectionItem->set('field_manufacturer', $dmanufacturer);
            $fieldCollectionItem->set('field_model', $dmodel);
            $fieldCollectionItem->set('field_url', $durl);

            $node->field_allocated_devices->appendItem(["field_collection_item" => $fieldCollectionItem]);
        }

        //CHANGE WORKFLOW STATE
        $change_state = ($current_state != "project_workflow_devices_allocated");
        if ($change_state)
        {
            //execute transition (save in history)
            $next_state = "project_workflow_devices_allocated";
            $transition =  WorkflowTransition::create([$current_state]);
            $transition->setValues($next_state, $uid = NULL, $timestamp = REQUEST_TIME, t("Devicehub: devices allocated"));
            $transition->setTargetEntity($node);
            $transition->execute(true);
            //change node state
            $node->get("field_workflow")->setValue($next_state);
        }

        //save node
        $node->save();

        //response data
        $response["status"] = "OK";
        $response["message"] = t("@n devices allocated", array("@n" => count($data["devices"])));
        if ($change_state)
            $response["message"] .= " | " . t("Project state changed to: @s",array("@s" => $next_state));

        //LOG
        \Drupal::logger("transferhub_devicehub")->info("REST SERVER: SUCCESS: ".$response["message"]);
        return new ResourceResponse($response, 201);
    }


    /**
     * Responds to GET requests.
     *
     * Returns a watchdog log entry for the specified ID.
     *
     *
     * @return \Drupal\rest\ResourceResponse
     *   The response containing the log entry.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *   Thrown when the log entry was not found.
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *   Thrown when no log entry was provided.
     */
    public function get() {

        $record["status"] = "ok";
        $record["message"] = "ALLOCATE you rock";
        if (!empty($record)) {
            return new ResourceResponse($record);
        }
        //throw new BadRequestHttpException(t('Something went wrong'));
        return new ResourceResponse($record);
    }

    public function put($type = null, $request) {
        //var_dump($type);
        //var_dump($request);

        //$response["type"] = $type;
        $response["data"] = json_decode($request->getContent(), true);

        //$request_content_array =  json_decode($request->getContent(), true);
        //$data = $request_content_array["data"];

        $response["status"] = "test OK";

        return new ResourceResponse($response, 201);
    }
}