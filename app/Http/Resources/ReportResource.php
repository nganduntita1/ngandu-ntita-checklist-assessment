<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'auditor'      => $this->when(
                $this->relationLoaded('auditor'),
                fn () => ['name' => $this->auditor?->name]
            ),
            'template'     => $this->when(
                $this->relationLoaded('template'),
                fn () => ['title' => $this->template?->title]
            ),
            'status'       => $this->status,
            'completed_at' => $this->completed_at,
            'created_at'   => $this->created_at,
        ];
    }
}
