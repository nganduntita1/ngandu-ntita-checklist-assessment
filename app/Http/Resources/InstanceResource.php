<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'template'     => new TemplateResource($this->whenLoaded('template')),
            'auditor'      => new UserResource($this->whenLoaded('auditor')),
            'status'       => $this->status,
            'completed_at' => $this->completed_at,
            'answers'      => AnswerResource::collection($this->whenLoaded('answers')),
            'created_at'   => $this->created_at,
        ];
    }
}
