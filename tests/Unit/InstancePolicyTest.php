<?php

use App\Models\ChecklistInstance;
use App\Models\User;
use App\Policies\InstancePolicy;

// ---------------------------------------------------------------------------
// InstancePolicy — no DB needed, instantiate policy directly
// ---------------------------------------------------------------------------

// Helper: build an unsaved User with a specific id (bypasses $fillable guard)
function makeUser(int $id, string $role): User
{
    $user = new User();
    $user->forceFill(['id' => $id, 'role' => $role]);
    return $user;
}

// Helper: build an unsaved ChecklistInstance with specific attributes
function makeInstance(int $auditorId, string $status): ChecklistInstance
{
    $instance = new ChecklistInstance();
    $instance->forceFill(['auditor_id' => $auditorId, 'status' => $status]);
    return $instance;
}

describe('InstancePolicy — owner auditor', function () {

    it('owner auditor passes view gate', function () {
        $policy   = new InstancePolicy();
        $auditor  = makeUser(1, 'auditor');
        $instance = makeInstance(1, 'draft');

        expect($policy->view($auditor, $instance))->toBeTrue();
    });

    it('owner auditor passes update gate on draft instance', function () {
        $policy   = new InstancePolicy();
        $auditor  = makeUser(1, 'auditor');
        $instance = makeInstance(1, 'draft');

        expect($policy->update($auditor, $instance))->toBeTrue();
    });
});

describe('InstancePolicy — non-owner auditor', function () {

    it('non-owner auditor fails view gate', function () {
        $policy   = new InstancePolicy();
        $auditor  = makeUser(2, 'auditor');
        $instance = makeInstance(1, 'draft');   // owned by user 1

        expect($policy->view($auditor, $instance))->toBeFalse();
    });

    it('non-owner auditor fails update gate', function () {
        $policy   = new InstancePolicy();
        $auditor  = makeUser(2, 'auditor');
        $instance = makeInstance(1, 'draft');   // owned by user 1

        expect($policy->update($auditor, $instance))->toBeFalse();
    });
});

describe('InstancePolicy — completed instance', function () {

    it('owner auditor fails update gate on completed instance', function () {
        $policy   = new InstancePolicy();
        $auditor  = makeUser(1, 'auditor');
        $instance = makeInstance(1, 'completed');

        expect($policy->update($auditor, $instance))->toBeFalse();
    });

    it('owner auditor still passes view gate on completed instance', function () {
        $policy   = new InstancePolicy();
        $auditor  = makeUser(1, 'auditor');
        $instance = makeInstance(1, 'completed');

        expect($policy->view($auditor, $instance))->toBeTrue();
    });
});
