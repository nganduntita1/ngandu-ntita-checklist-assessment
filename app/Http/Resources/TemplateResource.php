<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'status'          => $this->status,
            'questions_count' => $this->whenCounted('questions'),
            'questions'       => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at'      => $this->created_at,
        ];
    }
}
