<?php

namespace  App\Libraries;

use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use GuzzleHttp\Exception\BadResponseException;

class ConsumeAPILibrary
{
    protected $headers;
    
    protected $body;
    
    protected $type;
    
    protected $response;
    
    protected $exceptionResponse;
    
    const POST = 'POST';
    
    const DELETE = 'DELETE';
    
    const GET = 'GET';
    
    private static $clientObject;
    
    
    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }
    
    /**
     * Function to set all requirements before API hit.
     *
     * @param string $type
     * @param string $url
     * @param array $body
     * @param array $headers
     * @return \App\Libraries\ConsumeAPILibrary
     */
    public function request($type, $url, $body = array(), $headers = array())
    {
        if (!empty($headers)) {
            $this->setHeaders($headers);
        }
        
        $this->executeGuzzle($type, $url, $body);
        return $this;
    }
    
    /**
     * Function to set headers.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }
    
    /**
     * Function to get headers.
     *
     * @return array
     */
    public function getHeaders():array
    {
        return $this->headers;
    }
    
    /**
     * Function to hit external API.
     *
     * @param string $type
     * @param string $url
     * @param array $body
     * @throws \Exception
     */
    private function executeGuzzle($type, $url, $body)
    {
        try {
            //create singleston guizzle object
            $headers = $this->getHeaders();
            $response = null;
            self::$clientObject = $this->getGuzzleClientObject();
            $startTime = microtime(true);
            if ($type == self::POST) {
                info('request initiated to ' . $url);
                info('paylod', $body);
                $response = self::$clientObject->post($url, [
                    'headers' => $headers,
                    'json' => $body
                ]);
            } elseif ($this->type == self::DELETE) {
                
            } elseif ($type == self::GET) {
                info('request initiated to ' . $url);
//                 if (!empty($body)) {
//                     $response = self::$clientObject->get($url, ['headers' => $headers, 'query' => $bo]);
//                 } else{
                    $response = self::$clientObject->get($url, ['headers' => $headers]);
             //   }
            }
            $this->response = $response->getBody()->getContents();
            info("Time in hitting API   ". (microtime(true) - $startTime) ." seconds");
            info("response", json_decode($this->response, true));
            
        } catch (BadResponseException $exception) {
            $this->response = $exception->getResponse()->getBody(true)->getContents();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get guzzle client object.
     *
     * @return \GuzzleHttp\Client
     */
    private function getGuzzleClientObject()
    {
        if (self::$clientObject == null) {
            self::$clientObject = new Client();
        }
        return self::$clientObject;
    }
    
    /**
     * Getter function for API response.
     *
     * @return string
     */
    protected function getAPIResponse()
    {
        return $this->response;
    }
}
