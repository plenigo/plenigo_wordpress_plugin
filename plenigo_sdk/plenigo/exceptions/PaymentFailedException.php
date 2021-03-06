<?php

namespace plenigo\exceptions;

require_once __DIR__ . '/../models/ErrorDetail.php';

use plenigo\models\ErrorDetail;
use plenigo\PlenigoException;

/**
 * <p>
 * This represents any exception that can come from
 * the plenigo API or SDK.
 * </p>
 */
class PaymentFailedException extends PlenigoException
{

    /**
     * Array of errors.
     */
    private $errorDetail = array();

    /**
     * This constructor is used when an error code
     * has been obtained from the server.
     *
     * @param string $message The response message
     * @param int $code The response code
     * @param \Exception $previous The previous Exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * get the error array
     *
     * @return array
     */
    public function &getErrors()
    {
        return $this->errorDetail;
    }

    /**
     * Adds an {@link plenigo\models\ErrorDetail ErrorDetail} object created from this exception,
     * given the name and description parameters
     *
     * @param string $name the name of the error
     * @param string $description the english description of the error found
     */
    public function addErrorDetail($name, $description)
    {
        array_push($this->errorDetail, new ErrorDetail($name, $description));
    }

    /**
     * Clears the entire ErrorDetail array
     */
    public function clearErrorDetail()
    {
        $this->errorDetail = array();
    }

}