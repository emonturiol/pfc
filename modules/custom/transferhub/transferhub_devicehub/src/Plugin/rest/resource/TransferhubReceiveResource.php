<?php

namespace Drupal\transferhub_devicehub\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Something.
 *
 * @RestResource(
 *   id = "transferhub_receive",
 *   label = @Translation("Receive devices for a project"),
 *   uri_paths = {
 *     "canonical" = "/transferhub/events/devices/receive",
 *     "https://www.drupal.org/link-relations/create" = "/transferhub/events/devices/receive"
 *   }
 * )
 */
class TransferhubReceiveResource extends ResourceBase {

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
        $record["message"] = "RECEIVE you rock";
        if (!empty($record)) {
            return new ResourceResponse($record);
        }
        //throw new BadRequestHttpException(t('Something went wrong'));
        return new ResourceResponse($record);
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
    public function post() {

        $record["status"] = "ok";
        $record["message"] = "RECEIVE you rock";
        if (!empty($record)) {
            return new ResourceResponse($record);
        }
        //throw new BadRequestHttpException(t('Something went wrong'));
        return new ResourceResponse($record);
    }
}
