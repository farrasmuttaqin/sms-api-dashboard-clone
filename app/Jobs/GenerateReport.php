<?php

namespace Firstwap\SmsApiDashboard\Jobs;

use Exception;
use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Libraries\Report\WriterReportFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * instance of WriterReportFile class
     *
     * @var WriterReportFile
     */
    public $generator;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Report $report, $timezone)
    {
        $this->generator = new WriterReportFile($report, $timezone);
    }

    /**
     * Execute the job.
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function handle()
    {
        $this->generator->generate();
    }

    /**
     * The job failed to process.
     *
     * @codeCoverageIgnore
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::error($exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
}
