<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserLogin extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name'=>$this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'organization_id'=>$this->organization_id,
            'gender'=>$this->gender,
            'role' => $this->role_id,
            'image'=>$this->image,
            'timezone'=>$this->timezone,
            'api_token'=>$this->api_token,
        ];
    }
}
