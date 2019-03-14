<?php
/**
 * Cmsbox.fr Magento 2 Monetico Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Monetico
 * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Monetico\Gateway\Http;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Cmsbox\Monetico\Gateway\Processor\Connector;

class Client
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Client constructor.
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->curl            = $curl;

        // Launch functions
        $this->addHeaders();
    }

    /**
     * Adds the request headers.
     */
    private function addHeaders()
    {
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Accept', 'application/json');
    }

    /**
     * Encode the response to JSON format.
     */
    private function formatResponse($response)
    {
        return isset($response) ? (array) json_decode($response) : null;
    }

    /**
     * Returns a prepared post response.
     */
    public function getPostResponse($url, $params)
    {
        // Send the request
        $response = $this->post($url, $params);

        // Format the response
        $response = $this->formatResponse($response);

        return $response;
    }

    /**
     * Returns a prepared get response.
     */
    public function getGetResponse($url)
    {
        // Send the request
        $response = $this->get($url);

        // Format the response
        $response = $this->formatResponse($response);

        return $response;
    }

    public function post($url, $params)
    {
        // Send the CURL POST request
        $this->curl->post($url, json_encode($params));

        // Return the response
        return $this->curl->getBody();
    }
 
    public function get($url)
    {
        // Send the CURL GET request
        $this->curl->get($url);

        // Return the response
        return $this->curl->getBody();
    }

    public function setOption($name, $value)
    {
        $this->curl->setOption($name, $value);
    }
}
