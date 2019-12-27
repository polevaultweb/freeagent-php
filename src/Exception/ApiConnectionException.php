<?php

namespace Polevaultweb\FreeAgent\Exception;

/**
 * ApiConnection is thrown in the event that the SDK can't connect to FreeAgents's
 * servers. That can be for a variety of different reasons from a downed
 * network to a bad TLS certificate.
 *
 * @package Polevaultweb\FreeAgent\Exception
 */
class ApiConnectionException extends ApiErrorException
{
}
