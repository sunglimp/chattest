<?php

namespace  App\Libraries;

class TMSAPI extends ConsumeAPILibrary
{
    
    public function getResponse()
    {
        try {
            $response = $this->getAPIResponse();
            $response = json_decode($response, true);
            
            $status = $response['success'] ?? null;
        
            if ($status == true) {
                info("success", $response);
                $responseData = $response['data'] ?? array();
                return array(
                    'status' => true,
                    'data'   => $responseData
                );
                return $responseData;
            } else {
                info("fail", $response);
                return array(
                    'status' => false,
                    'errorCode' => $response['status'] ?? 0
                );
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
