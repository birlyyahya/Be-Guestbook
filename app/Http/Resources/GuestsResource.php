<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class GuestsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_name'=>$this->event->name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'organization' => $this->organization,
            'code' => $this->code,
            'qr_generated' => $this->qr_generated,
            'confirm_c' => $this->status === 'invited' ? Hashids::encode($this->id) : null,
            'available_date' => $this->available_date,
            'status' => $this->status,
            'check_in_time' => $this->check_in_time,
        ];
    }
}
