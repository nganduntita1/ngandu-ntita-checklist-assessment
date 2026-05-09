<?php

use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Policies\TemplatePolicy;

// ---------------------------------------------------------------------------
// TemplatePolicy — no DB needed, instantiate policy directly
// ---------------------------------------------------------------------------

describe('TemplatePolicy — admin', function () {

    it('admin passes viewAny gate', function () {
        $policy = new TemplatePolicy();
        $admin  = new User(['role' => 'admin']);

        expect($policy->viewAny($admin))->toBeTrue();
    });

    it('admin passes view gate', function () {
        $policy   = new TemplatePolicy();
        $admin    = new User(['role' => 'admin']);
        $template = new ChecklistTemplate();

        expect($policy->view($admin, $template))->toBeTrue();
    });

    it('admin passes create gate', function () {
        $policy = new TemplatePolicy();
        $admin  = new User(['role' => 'admin']);

        expect($policy->create($admin))->toBeTrue();
    });

    it('admin passes update gate', function () {
        $policy   = new TemplatePolicy();
        $admin    = new User(['role' => 'admin']);
        $template = new ChecklistTemplate();

        expect($policy->update($admin, $template))->toBeTrue();
    });

    it('admin passes delete gate', function () {
        $policy   = new TemplatePolicy();
        $admin    = new User(['role' => 'admin']);
        $template = new ChecklistTemplate();

        expect($policy->delete($admin, $template))->toBeTrue();
    });
});

describe('TemplatePolicy — auditor', function () {

    it('auditor passes viewAny gate', function () {
        $policy  = new TemplatePolicy();
        $auditor = new User(['role' => 'auditor']);

        expect($policy->viewAny($auditor))->toBeTrue();
    });

    it('auditor passes view gate', function () {
        $policy   = new TemplatePolicy();
        $auditor  = new User(['role' => 'auditor']);
        $template = new ChecklistTemplate();

        expect($policy->view($auditor, $template))->toBeTrue();
    });

    it('auditor fails create gate', function () {
        $policy  = new TemplatePolicy();
        $auditor = new User(['role' => 'auditor']);

        expect($policy->create($auditor))->toBeFalse();
    });

    it('auditor fails update gate', function () {
        $policy   = new TemplatePolicy();
        $auditor  = new User(['role' => 'auditor']);
        $template = new ChecklistTemplate();

        expect($policy->update($auditor, $template))->toBeFalse();
    });

    it('auditor fails delete gate', function () {
        $policy   = new TemplatePolicy();
        $auditor  = new User(['role' => 'auditor']);
        $template = new ChecklistTemplate();

        expect($policy->delete($auditor, $template))->toBeFalse();
    });
});
