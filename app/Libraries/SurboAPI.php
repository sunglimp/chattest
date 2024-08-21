<?php

namespace  App\Libraries;

class SurboAPI extends ConsumeAPILibrary
{
    
    public function getResponse()
    {
        try {
            $response = $this->getAPIResponse();
    
            $response = json_decode($response, true);
            $status = $response['status'] ?? null;
            if (!empty($status)) {
                if ($status === true) {
                    info("success", $response);
                    $responseData = $response['data'] ?? array();
                    return $responseData;
                } else {
                    info("fail", $response);
                    return $response;
    
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
