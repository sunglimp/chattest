<?php

namespace  App\Libraries;

class MlModelAPI extends ConsumeAPILibrary
{

    public function getResponse()
    {
        $response = $this->getAPIResponse();
        return $response;

    }
}
