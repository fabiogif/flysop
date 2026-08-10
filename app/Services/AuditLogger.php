<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(string $description, ?Model $subject = null, array $properties = []): void
    {
        $user = Auth::user();
        $properties = array_merge([
            'tenant_id' => $user?->tenant_id,
        ], $properties);

        $activity = activity()
            ->withProperties($properties)
            ->causedBy($user);

        if ($subject) {
            $activity->performedOn($subject);
        }

        $activity->log($description);
    }
}
