<?php

namespace App\Http\Controllers;

use App\Models\SurveyDomain;
use App\Models\SurveyItem;
use App\Models\SurveyResponse;
use App\Models\SurveySubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    // ── GET /api/survey/domains ────────────────────────────────────────────────
    public function domains(): JsonResponse
    {
        $domains = SurveyDomain::orderBy('sort_order')->get();
        return response()->json(['domains' => $domains]);
    }

    // ── GET /api/survey/items ──────────────────────────────────────────────────
    public function items(): JsonResponse
    {
        $items = SurveyItem::where('is_active', true)
            ->with('domain:id,code,name,color')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($item) => [
                'id'          => $item->id,
                'item_code'   => $item->item_code,
                'statement'   => $item->statement,
                'domain_code' => $item->domain->code,
                'domain_name' => $item->domain->name,
                'domain_color'=> $item->domain->color,
                'sort_order'  => $item->sort_order,
            ]);

        return response()->json([
            'items' => $items,
            'scale' => [
                1 => 'Strongly Disagree',
                2 => 'Disagree',
                3 => 'Agree',
                4 => 'Strongly Agree',
            ],
        ]);
    }

    // ── POST /api/survey/submit ────────────────────────────────────────────────
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id'  => 'nullable|exists:class_groups,id',
            'responses' => 'required|array|min:50|max:50',
            'responses.*.item_id'        => 'required|exists:survey_items,id',
            'responses.*.response_value' => 'required|integer|between:1,4',
        ]);

        $student = $request->user();

        // Check student hasn't submitted for this class already today (optional guard)
        $existing = SurveySubmission::where('student_id', $student->id)
            ->when($data['class_id'] ?? null, fn ($q, $cid) => $q->where('class_id', $cid))
            ->whereDate('completed_at', today())
            ->first();

        if ($existing) {
            return response()->json([
                'message'       => 'You have already submitted today.',
                'submission_id' => $existing->id,
            ], 422);
        }

        // ── Score calculation ──────────────────────────────────────────────────
        $responseMap = collect($data['responses'])->keyBy('item_id');
        $items       = SurveyItem::where('is_active', true)->with('domain')->get();

        $domainScores = ['M' => 0, 'R' => 0, 'P' => 0, 'T' => 0, 'O' => 0];

        foreach ($items as $item) {
            $code  = $item->domain->code;
            $value = $responseMap[$item->id]['response_value'] ?? 0;
            $domainScores[$code] += $value;
        }

        $totalScore = array_sum($domainScores);

        // Simple logit approximation: map raw score (50–200) to logit scale (0–140)
        // Rasch-calibrated conversion would come from WINSTEPS; here we use a linear approximation
        $convertToLogit = function (int $raw, int $maxRaw): float {
            // Map to 0–100 then to logit range 20–120
            $proportion = ($raw - $maxRaw * 0.25) / ($maxRaw * 0.75);
            $proportion = max(0.01, min(0.99, $proportion));
            $logit      = log($proportion / (1 - $proportion)) * 20 + 70;
            return round($logit, 2);
        };

        $logitTotal = $convertToLogit($totalScore, 200);

        DB::transaction(function () use (
            $student, $data, $domainScores, $totalScore, $logitTotal,
            $convertToLogit, &$submission, $responseMap, $items
        ) {
            $submission = SurveySubmission::create([
                'student_id'   => $student->id,
                'class_id'     => $data['class_id'] ?? null,
                'total_score'  => $totalScore,
                'logit_score'  => $logitTotal,
                'completed_at' => now(),
                'score_m' => $domainScores['M'],
                'score_r' => $domainScores['R'],
                'score_p' => $domainScores['P'],
                'score_t' => $domainScores['T'],
                'score_o' => $domainScores['O'],
                'logit_m' => $convertToLogit($domainScores['M'], 40),
                'logit_r' => $convertToLogit($domainScores['R'], 40),
                'logit_p' => $convertToLogit($domainScores['P'], 40),
                'logit_t' => $convertToLogit($domainScores['T'], 40),
                'logit_o' => $convertToLogit($domainScores['O'], 40),
            ]);

            $records = [];
            foreach ($data['responses'] as $resp) {
                $records[] = [
                    'submission_id'  => $submission->id,
                    'item_id'        => $resp['item_id'],
                    'response_value' => $resp['response_value'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            SurveyResponse::insert($records);
        });

        return response()->json([
            'message'       => 'Survey submitted successfully.',
            'submission_id' => $submission->id,
            'total_score'   => $totalScore,
            'logit_score'   => $logitTotal,
            'ability_level' => $submission->ability_level,
            'domain_scores' => $submission->domain_scores,
        ], 201);
    }

    // ── GET /api/survey/my-submissions ────────────────────────────────────────
    public function mySubmissions(Request $request): JsonResponse
    {
        $submissions = SurveySubmission::where('student_id', $request->user()->id)
            ->with('classGroup:id,name,code')
            ->orderByDesc('completed_at')
            ->get()
            ->map(fn ($s) => [
                'id'            => $s->id,
                'total_score'   => $s->total_score,
                'logit_score'   => $s->logit_score,
                'ability_level' => $s->ability_level,
                'domain_scores' => $s->domain_scores,
                'class'         => $s->classGroup,
                'completed_at'  => $s->completed_at,
            ]);

        return response()->json(['submissions' => $submissions]);
    }

    // ── GET /api/survey/submission/{id} ───────────────────────────────────────
    public function submission(Request $request, int $id): JsonResponse
    {
        $submission = SurveySubmission::where('id', $id)
            ->where('student_id', $request->user()->id)
            ->with(['responses.item.domain', 'classGroup:id,name'])
            ->firstOrFail();

        return response()->json([
            'submission'    => [
                'id'            => $submission->id,
                'total_score'   => $submission->total_score,
                'logit_score'   => $submission->logit_score,
                'ability_level' => $submission->ability_level,
                'domain_scores' => $submission->domain_scores,
                'class'         => $submission->classGroup,
                'completed_at'  => $submission->completed_at,
            ],
            'responses' => $submission->responses->map(fn ($r) => [
                'item_code'      => $r->item->item_code,
                'statement'      => $r->item->statement,
                'domain_code'    => $r->item->domain->code,
                'response_value' => $r->response_value,
            ]),
        ]);
    }
}
