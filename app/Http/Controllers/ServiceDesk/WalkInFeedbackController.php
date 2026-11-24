<?php

namespace App\Http\Controllers\ServiceDesk;

use App\Http\Controllers\Controller;
use App\Models\WalkInFeedback;
use App\Support\ExcelWorkbook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalkInFeedbackController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $feedback = WalkInFeedback::query()
            ->whereBetween('visited_at', [$from, $to])
            ->orderByDesc('visited_at')
            ->paginate(25)
            ->withQueryString();

        return view('service-desk.feedback.index', [
            'feedback' => $feedback,
            'from' => $from->format('Y-m-d\TH:i'),
            'to' => $to->format('Y-m-d\TH:i'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);

        $rows = [
            [
                'ID',
                'Walk-in date & time',
                'Client name',
                'Email',
                'Recommend score (0-10)',
                'Reason for rating',
                'Satisfaction (1-5)',
                'Satisfaction label',
                'How we can improve',
                'Submitted at',
            ],
        ];

        WalkInFeedback::query()
            ->whereBetween('visited_at', [$from, $to])
            ->orderBy('visited_at')
            ->get()
            ->each(function (WalkInFeedback $item) use (&$rows) {
                $rows[] = [
                    $item->id,
                    $item->visited_at->format('Y-m-d H:i'),
                    $item->client_name ?? '',
                    $item->client_email ?? '',
                    $item->recommend_score,
                    $item->rating_reason,
                    $item->satisfaction_level,
                    $item->satisfactionLabel(),
                    $item->improvement_suggestions ?? '',
                    $item->created_at->format('Y-m-d H:i'),
                ];
            });

        $workbook = (new ExcelWorkbook)->addSheet('Walk-in feedback', $rows);

        $filename = sprintf(
            'ppmz-walk-in-feedback-%s-to-%s.xlsx',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        return response()->streamDownload(
            fn () => print($workbook->toXlsx()),
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(Request $request): array
    {
        $fromInput = $request->input('from', now()->startOfMonth()->format('Y-m-d\TH:i'));
        $toInput = $request->input('to', now()->endOfMonth()->format('Y-m-d\TH:i'));

        $from = Carbon::parse($fromInput)->startOfMinute();
        $to = Carbon::parse($toInput)->endOfMinute();

        if ($to->lt($from)) {
            $to = $from->copy()->endOfDay();
        }

        return [$from, $to];
    }
}