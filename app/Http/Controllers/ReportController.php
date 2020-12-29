<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers;

use Carbon\Carbon;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ReportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{

    /**
     * ReportRepository instance
     * This variable use to show and process data
     *
     * @var ReportRepository
     */
    protected $repo;

    /**
     * Create a new ReportController instance.
     *
     * @return void
     */
    public function __construct(ReportRepository $report)
    {
        $this->middleware('ajax')->only(['table','destroy']);
        $this->repo = $report;
    }

    /**
     * Show the report page.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize('index', Report::class);

        return view('reports.index');
    }

    /**
     * Retry Generate report
     *
     * @return Response
     */
    public function regenerate($reportId)
    {
        $this->authorize('generate', Report::class);

        $report = $this->repo->find($reportId);

        if (is_null($report)) {
            return redirect()
                ->route('report.index')
                ->withErrors(['notfound' => trans('validation.exists', ['attribute' => 'report'])]);
        }

        if ($report->generate_status === 2) {
            return redirect()
                    ->route('report.index')
                    ->withErrors(['finished' => trans('app.exists', ['name' => 'Report'])]);
        }

        $this->repo->deleteReportFile($report->file_path);

        $this->repo->generateReport($report);

        return redirect()
                ->route('report.index')
                ->with(['alert-success' => trans('app.process_generate')]);
    }

    /**
     * Download report file.
     *
     * @param  integer $reportId
     * @return Response
     */
    public function download($reportId)
    {
        $report = $this->repo->find($reportId);

        if (is_null($report)) {
            return redirect()
                ->route('report.index')
                ->withErrors(['notfound' => trans('validation.exists', ['attribute' => 'report'])]);
        }

        $this->authorize('download', $report);

        if (!Storage::disk('report')->exists($report->file_path)) {
            return redirect()
                    ->route('report.index')
                    ->withErrors(['no_file' => trans('app.no_file')]);
        }

        $pathToFile = storage_path('reports/' . $report->file_path);
        $fileExtension = pathinfo($pathToFile, PATHINFO_EXTENSION);
        $name = $report->report_name . "." . $fileExtension;

        return response()->download($pathToFile, $name);
    }

    /**
     * Get report that still on progress
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function onProcessReport()
    {
        $this->authorize('index', Report::class);

        $reports = $this->repo->onProcess();

        foreach ($reports as $report) {
            if ($report->isProcessing() === false
                && $report->generate_status === Report::REPORT_PROCESS
                && $report->percentage < 1
            ) {
                $this->repo->failedReport($report);
            }
        }

        return response()->json($reports);
    }


    /**
     * Get report that still on progress
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelReport(Request $request)
    {
        $this->authorize('generate', Report::class);
        $this->validate($request, ['report_id' => 'required']);

        $report = $this->repo->find($request->report_id);

        if (is_null($report) || $report->generate_status !== Report::REPORT_PROCESS) {
            return abort(404);
        }

        $report->cancelReport();

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => trans('app.success_request_cancel')
        ]);
    }


    /**
     * Display a listing of user
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function table(Request $request)
    {
        $search = $request->all();
        $data = $this->repo->table($search);

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('generate', Report::class);

        return view('reports.form');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $reportId
     * @return \Illuminate\Http\Response
     */
    public function destroy($reportId)
    {
        $this->authorize('delete', Report::class);

        $deleted = $this->repo->delete($reportId);

        return response()->json(['success' => $deleted]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->authorize('generate', Report::class);

        $this->validate($request, $this->validationRulesForCreate());

        $input = $this->processRequestInput($request);
        $saved = $this->repo->save($input);

        return $saved
                ? redirect()
                    ->route('report.index')
                    ->with('alert-success', trans('validation.success_save', ['name' => 'report ' . $input['report_name']]))
                : back()
                    ->withInput()
                    ->withErrors(['failed_save' => trans('validation.failed_save', ['name' => 'report'])]);
    }

    /**
     * Validation rules for store request
     *
     * @return array
     */
    protected function validationRulesForCreate()
    {
        return [
            'report_name' => ['nullable', 'regex:/(^[A-Za-z0-9\s\-\_]+$)+/', 'max:100'],
            'file_type' => ['required', 'string','in:xlsx,csv'],
            'message_status' => ['required', 'string'],
            'start_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'api_users' => ['required', 'string'],
        ];
    }

    /**
     * Process the input value from request before store to database
     *
     * @param Request $request
     * @return array
     */
    protected function processRequestInput(Request $request)
    {
        $input = $request->all();

        /**
         * Get user timezone value
         */
        $timezone = auth()->user()->timezone;

        $input['api_users'] = array_filter(explode(',', $input['api_users']));

        if (!$request->get('report_name')) {
            $input['report_name'] = 'Report_API_'.Carbon::now($timezone)->format('dmY_His');
        }

        /**
         * Validate start date
         * If start date 90 days before, it will change to 90 days before.
         */
        $startDate = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $request->start_date,
                        $timezone
                    );
        $minDate = Carbon::now($timezone)->subDays(90)->startOfDay();
        $input['start_date'] = $startDate->lte($minDate)
                                ? $minDate
                                : $startDate;

        /**
         * Validate end date
         * If end date less than start date, it will use same as start date
         */
        $endDate = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $request->end_date,
                        $timezone
                );
        $input['end_date'] = $endDate->lte($input['start_date'])
                                ? clone $input['start_date']
                                : $endDate;

        $input['user_timezone'] = $timezone;

        return tap($input, function ($item) use ($timezone) {
            $item['start_date']
                ->setTimezone($timezone);
            $item['end_date']
                ->setTimezone($timezone);
        });
    }
}
