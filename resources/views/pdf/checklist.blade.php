<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist Report — {{ $instance->template->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #ffffff;
            line-height: 1.5;
        }

        /* Page header */
        .page-header {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 14px 20px;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .page-header p {
            font-size: 10px;
            margin-top: 2px;
            opacity: 0.85;
        }

        /* Main content wrapper */
        .content {
            padding: 0 20px 20px 20px;
        }

        /* Template info section */
        .template-section {
            border: 1px solid #d0d7de;
            border-radius: 4px;
            padding: 14px 16px;
            margin-bottom: 20px;
            background-color: #f6f8fa;
        }

        .template-section h2 {
            font-size: 15px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 6px;
        }

        .template-section p {
            font-size: 11px;
            color: #444;
            margin-bottom: 4px;
        }

        /* Meta info table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .meta-table td {
            padding: 6px 10px;
            font-size: 11px;
            border: 1px solid #d0d7de;
        }

        .meta-table td.label {
            font-weight: bold;
            background-color: #f0f4f8;
            width: 30%;
            color: #333;
        }

        .meta-table td.value {
            color: #1a1a1a;
        }

        /* Questions section */
        .questions-heading {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 2px solid #1e3a5f;
        }

        /* Individual question block */
        .question-block {
            margin-bottom: 12px;
            border: 1px solid #d0d7de;
            border-radius: 3px;
            overflow: hidden;
        }

        .question-header {
            background-color: #eef2f7;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: bold;
            color: #1e3a5f;
            border-bottom: 1px solid #d0d7de;
        }

        .question-number {
            display: inline-block;
            background-color: #1e3a5f;
            color: #ffffff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
            font-size: 10px;
            margin-right: 6px;
        }

        .question-answer {
            padding: 8px 12px;
            font-size: 11px;
            color: #333;
            background-color: #ffffff;
        }

        .answer-label {
            font-weight: bold;
            color: #555;
            margin-right: 4px;
        }

        .no-answer {
            color: #999;
            font-style: italic;
        }

        /* Footer */
        .report-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #d0d7de;
            font-size: 10px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Page header --}}
    <div class="page-header">
        <h1>Compliance Checklist System</h1>
        <p>Automated Compliance Report</p>
    </div>

    <div class="content">

        {{-- Template info --}}
        <div class="template-section">
            <h2>{{ $instance->template->title }}</h2>
            @if($instance->template->description)
                <p>{{ $instance->template->description }}</p>
            @endif
        </div>

        {{-- Auditor & completion meta --}}
        <table class="meta-table">
            <tr>
                <td class="label">Auditor Name</td>
                <td class="value">{{ $instance->auditor->name }}</td>
            </tr>
            <tr>
                <td class="label">Auditor Email</td>
                <td class="value">{{ $instance->auditor->email }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">{{ ucfirst($instance->status) }}</td>
            </tr>
            <tr>
                <td class="label">Completion Date</td>
                <td class="value">
                    @if($instance->completed_at)
                        {{ $instance->completed_at->format('d F Y, H:i') }}
                    @else
                        <span class="no-answer">Not yet completed</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Report Generated</td>
                <td class="value">{{ now()->format('d F Y, H:i') }}</td>
            </tr>
        </table>

        {{-- Questions & Answers --}}
        <div class="questions-heading">Questions &amp; Answers</div>

        @php
            // Build a lookup map: question_id => answer_value
            $answerMap = $instance->answers->keyBy('question_id');
            $questions = $instance->template->questions->sortBy('sort_order');
        @endphp

        @forelse($questions as $index => $question)
            @php
                $answer = $answerMap->get($question->id);
                $answerValue = $answer ? $answer->answer_value : null;
            @endphp
            <div class="question-block">
                <div class="question-header">
                    <span class="question-number">{{ $index + 1 }}</span>
                    {{ $question->question_text }}
                    @if($question->required)
                        <span style="color:#c0392b; font-size:10px;"> *</span>
                    @endif
                </div>
                <div class="question-answer">
                    <span class="answer-label">Answer:</span>
                    @if(!is_null($answerValue) && $answerValue !== '')
                        {{ $answerValue }}
                    @else
                        <span class="no-answer">No answer</span>
                    @endif
                </div>
            </div>
        @empty
            <p style="color:#888; font-style:italic;">No questions found for this template.</p>
        @endforelse

        {{-- Footer --}}
        <div class="report-footer">
            Compliance Checklist System &mdash; Generated on {{ now()->format('d F Y') }}
        </div>

    </div>

</body>
</html>
