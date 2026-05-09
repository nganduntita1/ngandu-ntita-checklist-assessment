<?php

namespace Database\Seeders;

use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChecklistTemplateSeeder extends Seeder
{
    /**
     * Sample templates with their questions.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $templates = [
        [
            'title' => 'Health & Safety Compliance Audit',
            'description' => 'Comprehensive audit to ensure workplace health and safety standards are met.',
            'status' => 'active',
            'questions' => [
                ['question_text' => 'Are all fire exits clearly marked and unobstructed?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 1],
                ['question_text' => 'When was the last fire drill conducted?', 'answer_type' => 'text', 'required' => true, 'sort_order' => 2],
                ['question_text' => 'How many first aid kits are available on the premises?', 'answer_type' => 'number', 'required' => true, 'sort_order' => 3],
                ['question_text' => 'Describe any hazards identified during the inspection.', 'answer_type' => 'textarea', 'required' => false, 'sort_order' => 4],
                ['question_text' => 'Are all employees trained in emergency procedures?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 5],
            ],
        ],
        [
            'title' => 'Data Protection & GDPR Assessment',
            'description' => 'Assessment to verify compliance with data protection regulations including GDPR.',
            'status' => 'active',
            'questions' => [
                ['question_text' => 'Does the organisation have a documented data protection policy?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 1],
                ['question_text' => 'Who is the designated Data Protection Officer?', 'answer_type' => 'text', 'required' => true, 'sort_order' => 2],
                ['question_text' => 'Describe the process for handling data subject access requests.', 'answer_type' => 'textarea', 'required' => true, 'sort_order' => 3],
                ['question_text' => 'How many data breaches were reported in the last 12 months?', 'answer_type' => 'number', 'required' => true, 'sort_order' => 4],
                ['question_text' => 'Are data retention schedules documented and followed?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 5],
                ['question_text' => 'Provide details of any third-party data processors used.', 'answer_type' => 'textarea', 'required' => false, 'sort_order' => 6],
            ],
        ],
        [
            'title' => 'IT Security Audit',
            'description' => 'Review of IT security controls, policies, and incident response procedures.',
            'status' => 'active',
            'questions' => [
                ['question_text' => 'Is multi-factor authentication enabled for all critical systems?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 1],
                ['question_text' => 'When was the last penetration test conducted?', 'answer_type' => 'text', 'required' => true, 'sort_order' => 2],
                ['question_text' => 'How many security incidents were recorded in the past quarter?', 'answer_type' => 'number', 'required' => true, 'sort_order' => 3],
                ['question_text' => 'Describe the patch management process.', 'answer_type' => 'textarea', 'required' => true, 'sort_order' => 4],
            ],
        ],
        [
            'title' => 'Financial Controls Review',
            'description' => 'Internal review of financial controls, segregation of duties, and audit trails.',
            'status' => 'active',
            'questions' => [
                ['question_text' => 'Are financial approvals subject to dual authorisation?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 1],
                ['question_text' => 'What is the threshold for requiring board approval on expenditures?', 'answer_type' => 'text', 'required' => true, 'sort_order' => 2],
                ['question_text' => 'How many expense claims were flagged for review last quarter?', 'answer_type' => 'number', 'required' => false, 'sort_order' => 3],
                ['question_text' => 'Describe the reconciliation process for bank accounts.', 'answer_type' => 'textarea', 'required' => true, 'sort_order' => 4],
                ['question_text' => 'Are all financial transactions logged in the audit trail?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 5],
            ],
        ],
        [
            'title' => 'Environmental Compliance Check',
            'description' => 'Verification of environmental policies, waste management, and regulatory compliance.',
            'status' => 'inactive',
            'questions' => [
                ['question_text' => 'Does the organisation hold all required environmental permits?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 1],
                ['question_text' => 'What is the total waste generated (in kg) this month?', 'answer_type' => 'number', 'required' => true, 'sort_order' => 2],
                ['question_text' => 'Describe the waste segregation and recycling procedures in place.', 'answer_type' => 'textarea', 'required' => true, 'sort_order' => 3],
                ['question_text' => 'Name of the licensed waste disposal contractor.', 'answer_type' => 'text', 'required' => true, 'sort_order' => 4],
                ['question_text' => 'Have any environmental incidents been reported this quarter?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 5],
                ['question_text' => 'Provide details of any environmental incidents or near-misses.', 'answer_type' => 'textarea', 'required' => false, 'sort_order' => 6],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        foreach ($this->templates as $templateData) {
            $questions = $templateData['questions'];
            unset($templateData['questions']);

            $templateData['created_by'] = $admin->id;

            $template = ChecklistTemplate::firstOrCreate(
                ['title' => $templateData['title']],
                $templateData
            );

            // Only seed questions if the template was just created (no questions yet)
            if ($template->questions()->count() === 0) {
                foreach ($questions as $questionData) {
                    ChecklistQuestion::create(array_merge($questionData, [
                        'template_id' => $template->id,
                    ]));
                }
            }
        }
    }
}
