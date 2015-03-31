<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 30.03.2015
 * Time: 20:56
 */
namespace samsonframework\w3c;

use samsonframework\w3c\violation\Collection;

/**
 * W3C validator
 *
 * @package samsonframework\w3c
 * @author Vitalii Iehorov <egorov@samsonos.com>
 */
class Validator
{
    /** @var string URL for validating */
    protected $w3cUrl;

    /** @var bool Validation result status */
    protected $w3cStatus;

    /** @var int Amount of validation errors found */
    protected $w3cErrorsCount = 0;

    /** @var int Amount of validation warnings found */
    protected $w3cWarningsCount = 0;

    /** @var \samsonframework\w3c\violation\Collection W3C Errors collection */
    protected $w3cErrors = array();

    /** @var \samsonframework\w3c\violation\Collection W3C Warnings collection */
    protected $w3cWarnings = array();

    /** @var string Source URL for validating  */
    protected $sourceUrl;

    /**
     * @param string $sourceUrl Source URL for validating
     * @param string $w3cUrl W3C validator controller URL
     */
    public function __construct($sourceUrl, $w3cUrl = 'http://validator.w3.org/check')
    {
        $this->sourceUrl = $sourceUrl;
        $this->w3cUrl = $w3cUrl;
    }

    /**
     * Perform HTTP request
     * @param string $sourceUrl Source URL for validating
     * @returns string HTTP response results
     * @throws RequestException If W3C request has been failed
     */
    protected function httpRequest($sourceUrl)
    {
        // Build request URL with GET parameters
        $uri = $this->w3cUrl . '?' . http_build_query(array('output' => 'soap12', 'uri' => $sourceUrl));

        // Create a stream
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: W3C Validation bot\r\n"
            )
        );

        // Perform HTTP request
        $response = trim(file_get_contents($uri, false, stream_context_create($opts)));

        // If we have completed HTTP request
        if ($response === false) {
            // Throw http request failed exception
            throw new RequestException('W3C API http request failed');
        }

        return $response;
    }

    /**
     * W3C validator function
     * @throws \samsonframework\w3c\ParseException
     * @returns array
     */
    public function validate()
    {
        // Block errors reporting
        libxml_use_internal_errors(true);

        // W3C validator response
        $w3cResponse = simplexml_load_string($this->httpRequest($this->sourceUrl));

        // Get document namespaces declaration
        $nameSpaces = $w3cResponse->getNamespaces(true);

        // Check if we have received valid XML response
        if (!isset($nameSpaces['env'])) {
            throw new ParseException('XML parsing failed');
        }

        // Get validation data
        $validationResponse = $w3cResponse
            ->children($nameSpaces['env'])  // Get 'http://www.w3.org/2003/05/soap-envelope/'
            ->children($nameSpaces['m'])    // Get 'http://www.w3.org/2005/10/markup-validator'
            ->markupvalidationresponse;

        // Create XML errors elements
        $errors = new \SimpleXMLElement('<t></t>');
        if (isset($validationResponse->warnings)) {
            $errors = $validationResponse->errors->errorlist->error;
        }

        // Create XML warnings elements
        $warnings = new \SimpleXMLElement('<t></t>');
        if (isset($validationResponse->warnings)) {
            $warnings = $validationResponse->warnings->warninglist->warning;
        }

        // Create warnings collection
        $this->w3cWarnings = new Collection($warnings, __NAMESPACE__ . '\violation\Warning');

        // Create errors collection
        $this->w3cErrors = new Collection($errors, __NAMESPACE__ . '\violation\Error');

        // Set validation summary results
        $this->w3cStatus = (bool)$validationResponse->validity;
        $this->w3cErrorsCount = sizeof($this->w3cErrors);
        $this->w3cWarningsCount = sizeof($this->w3cWarnings);

        // Form response array
        return array(
            'validity' => $this->w3cStatus,
            'errorsCount' => $this->w3cErrorsCount,
            'errors' => $this->w3cErrors->toArray(),
            'warningsCount' => $this->w3cWarningsCount,
            'warnings' => $this->w3cWarnings->toArray(),
            'refferer' => $this->w3cUrl.'?uri='.$this->sourceUrl
        );
    }
}
