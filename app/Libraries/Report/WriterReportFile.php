<?php

namespace Firstwap\SmsApiDashboard\Libraries\Report;

use Exception;
use Box\Spout\Common\Type;
use Illuminate\Support\Carbon;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Writer\Style\StyleBuilder;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Libraries\Report\CanceledGenerateException;

class WriterReportFile
{

    /**
     * Writer instance to create a excel/csv file
     *
     * @var \Box\Spout\Writer\WriterInterface
     */
    protected $writer;

    /**
     * Model Report instance
     *
     * @var Report
     */
    protected $model;

    /**
     * Builder to get messages data
     *
     * @var Builder
     */
    protected $messageBuilder;

    /**
     * Message keys that should print to report
     *
     * @var array
     */
    protected $keys;

    /**
     * User timezone
     *
     * @var string
     */
    protected $timezone;

    /**
     * Report Filename
     *
     * @var string
     */
    protected $fileName;

    /**
     * Array of generated report file
     *
     * @var array
     */
    protected $reportFiles = [];

    /**
     * Initialize WriterReportFile instance
     *
     * @param Report $model
     */
    public function __construct(Report $model, $timezone = 'UTC')
    {
        $this->model    = $model;
        $this->timezone = $timezone;
        $this->fileName = $this->generateFileName();

        $this->createFolderReport();
    }

    /**
     * Generate Report File
     *
     * @return void
     */
    public function generate()
    {
        \DB::connection('api_dashboard')->enableQueryLog();
        \DB::connection('mysql_sms_api')->enableQueryLog();
        \DB::connection('mysql_bill_u_msg')->enableQueryLog();

        Message::$userTimezone = $this->timezone;

        try {
            $total = $this->getTotalData();

            if ($total === 0) {
                $this->updateModel(['generate_status' => Report::REPORT_FAILED]);
                $this->updateReportManifest(['status' => Report::REPORT_FAILED]);
                // Log
                Log::info("No Data for report ".$this->model->report_name);
                return;
            } else {
                // Log
                Log::info("Start Generate Report ". $this->model->report_name);
                Log::debug("PID: " . getmypid());
                Log::debug("Total Data: ".$total);

                $this->updateReportManifest([
                    'pid' => getmypid(),
                    'total' => $total,
                    'percentage' => 0,
                    'status' => Report::REPORT_PROCESS,
                    'fileName' => $this->fileName,
                ]);

                $this->updateModel([
                    'pid' => getmypid(),
                    'generate_status' => Report::REPORT_PROCESS
                ]);

                $messages = null;
                $counter    = 0;
                $maxRow     = config('report.max_row', 200000);
                $perBatch   = config('report.per_batch');
                \Log::info($this->getBuilder()->toSql());
                $this->getBuilder()->chunk($perBatch, function (&$messages) use (&$perBatch, &$counter, &$total, &$maxRow)
                {
                    if ($this->getManifest('status') === Report::REPORT_CANCELED)
                    {
                        $message = "The ".$this->model->report_name." report has been canceled";
                        throw new CanceledGenerateException($message);
                    }

                    $counter += $messages->count();
                    $part     = ceil($counter / $maxRow);

                    if (count($this->reportFiles) < $part)
                    {
                        $this->reportFiles[] = $this->initReportWriter($part);
                    }

                    $this->addRow($messages);

                    // calculate progress in percentage
                    $percentage = $counter / $total;
                    $this->updateReportManifest(['percentage' => $percentage]);

                    // Logger
                    Log::debug("Generate Report ".$this->model->report_name.": ".((int)($percentage * 100))."% $counter/$total");
                });
            }
        }
        catch (CanceledGenerateException $ex)
        {
            $this->updateModel(['generate_status' => Report::REPORT_CANCELED]);
            // Logger
            Log::warning($ex->getMessage());
        }
        catch (Exception $ex)
        {
            $this->updateModel(['generate_status' => Report::REPORT_FAILED]);
            $this->updateReportManifest(['status' => Report::REPORT_FAILED]);

            // Logger
            Log::error($ex->getMessage());
            Log::error($ex->getTraceAsString());
        }

        $this->close();

        // LOGGER
        // Get query log
        $logApiDashboard  = \DB::connection('api_dashboard')->getQueryLog();
        $logSmsApi        = \DB::connection('mysql_sms_api')->getQueryLog();
        // Flush query log
        \DB::connection('api_dashboard')->flushQueryLog();
        \DB::connection('mysql_sms_api')->flushQueryLog();
        // disabled query log
        \DB::connection('api_dashboard')->disableQueryLog();
        \DB::connection('mysql_sms_api')->disableQueryLog();

        // SMS API DASHBOARD QUERY LOG
        if (count($logApiDashboard) > 0) {
            Log::debug("SMS API DASHBOARD QUERY LOG :");
            $total = array_sum(array_column($logApiDashboard, 'time')) / 1000;
            Log::debug("Average Query Time (s): " . $total / count($logApiDashboard));
            Log::debug("Total Query Time (s): " . $total);
        }

        // SMS API v2 QUERY LOG
        if (count($logSmsApi) > 0) {
            Log::debug("SMS API v2 QUERY LOG :");
            $total = array_sum(array_column($logSmsApi, 'time')) / 1000;
            Log::debug("Average Query Time (s): " . $total / count($logSmsApi));
            Log::debug("Total Query Time (s): " . $total);
        }

        // INFO SUCCESS
        Log::info("Finish Generate Report ".$this->model->report_name);
    }


    /**
     * Initialize report writer
     *
     * @param Int $part
     * @return String
     */
    protected function initReportWriter($part = 1)
    {
        if (isset($this->writer))
        {
            $this->writer->close();
            unset($this->writer);
        }

        $fileType = $this->model->file_type;

        if ($fileType === Type::XLSX)
        {
            $this->writer = new XLSXWriter();
            $this->writer->setDefaultRowStyle($this->getExcelStyle());
            $this->writer->setTempFolder(config('report.temp_folder'));
        }
        else
        {
            $this->writer = WriterFactory::create(Type::CSV);
        }

        $folder     = config('report.temp_folder');
        $fileName   = $this->generateReportPartName($part);
        $filePath   = $folder . '/' . $fileName;

        $this->writer->openToFile($filePath);
        $this->writer->addRow($this->getHeader());

        if ($fileType === Type::XLSX && ($workbook = $this->writer->getBook()))
        {
            $this->updateReportManifest([
                'temp' => $workbook->getFileSystemHelper()->getRootFolder()
            ]);
        }

        return $filePath;
    }

    /**
     * insert messages data to excel row
     *
     * @param Illuminate\Database\Eloquent\Collection $messages
     * @return void
     */
    protected function addRow(&$messages)
    {
        foreach ($messages as &$msg) {

            if (!empty($msg->op_id)) $this->manipulateOperator($msg);

            $this->writer->addRow(
                $msg->only(
                    $this->getKeys()
                )
            );
        }
    }

    /**
     * Manipulate operator
     *
     * @return void
     */
    public function manipulateOperator(&$msg)
    {
        $operator = $msg->op_id;

        if ($operator == "EXCELCOM" || $operator == "AXIS"){$operator = "XL";}else if ($operator == "IM3" || $operator == "SATELINDO"){$operator = "INDOSAT";}else if ($operator == "SMART" || $operator == "MOBILE_8"){$operator = "SMART";}else if ($operator == "TELKOMSEL" || $operator == "TELKOMMOBILE"){$operator = "TELKOMSEL";}else if ($operator == "THREE"){$operator = "HUTCH";}

        $msg->op_id = $operator;
    }

    /**
     * Close and unset the report writer
     * Compress into a zip file for all xlsx/csv report files
     * Deleted the xlsx/csv files after compressed the reports
     * Update the report attributes
     *
     * @return void
     */
    protected function close()
    {
        if (isset($this->writer))
        {
            $this->writer->close();
        }

        $storage = Storage::disk('report');
        $status  = $this->getManifest('status');

        if (in_array($status, [Report::REPORT_FAILED, Report::REPORT_CANCELED]))
        {
            // Delete the generated reports
            array_map('unlink', $this->reportFiles);

            // Set attributes to update model
            $attributes = [
                'pid' => 0,
                'file_path' => null,
                'percentage' => 0
            ];
        }
        else
        {
            // Compress all generated reports file into zip file
            $reportZip = $this->createZipFile();

            // Delete the xlsx files
            array_map('unlink', $this->reportFiles);

            // Set attributes to update model
            $attributes = [
                'generated_at' => Carbon::now($this->timezone),
                'generate_status' => Report::REPORT_FINISHED,
                'pid' => 0,
                'file_path' => class_basename($reportZip),
            ];

        }

        $this->updateModel($attributes);

        unset($this->writer);
    }

    /**
     * Create a zip file and store to reports directory
     * Add all xlsx/csv reports file to zip file
     *
     * @return String   A string of zip file path
     */
    protected function createZipFile()
    {
        $dir            = config('report.folder');
        $zipName        = $this->fileName;
        $zipFilePath    = "$dir/$zipName.zip";
        $zip            = new \ZipArchive();

        if (($code = $zip->open($zipFilePath, \ZipArchive::CREATE)) !== true) {
            throw new Exception("Can't Create Zip file $code", 500);
        }

        $prefixReportName = $this->model->report_name;
        $partName         = count($this->reportFiles) > 1 ? "_PART_" : null;

        foreach ($this->reportFiles as $index => $file)
        {
            $zip->addFile($file, "$prefixReportName".($partName === null ? $partName : $partName . ( $index + 1 )).".{$this->model->file_type}");
        }

        $zip->close();

        Log::debug("ZIP path $zipFilePath");

        return $zipFilePath;
    }

    /**
     * Generate Report file name
     *
     * @param Int The number of part file
     * @return String   Name of report file
     */
    protected function generateReportPartName($part = 1)
    {
        $reportName = $this->fileName;
        $part       = "_PART_$part";
        $ext        = $this->model->file_type;

        return "$reportName$part.$ext";
    }

    /**
     * Generate Report file name
     *
     * @return string
     */
    protected function generateFileName()
    {
        return $this->getManifest('fileName') ?: md5($this->model->getKey() . microtime());
    }

    /**
     * Update manifest report
     *
     * @param array $data    Manifest data that will store to cache ['status', 'percentage', 'pid', 'total']
     * @return void
     */
    protected function updateReportManifest(array $data = [])
    {
        $this->model->updateManifest($data);
    }

    /**
     * Get manifest generator data
     *
     * @param string $key   Key of manifest ['status', 'pid', 'total', 'percentage']
     * @return Mixed
     */
    protected function getManifest($key)
    {
        return $this->model->getManifest($key);
    }

    /**
     * Get style for excel file
     *
     * @return \Box\Spout\Writer\Style\Style
     */
    protected function getExcelStyle()
    {
        return (new StyleBuilder())
           ->setFontName('Arial')
           ->setFontSize(10)
           ->build();
    }

    /**
     * Create report/temp folder if doesn't exists
     *
     * @return void
     */
    protected function createFolderReport()
    {

        $reportFolder = config('report.folder');

        if (!@is_dir($reportFolder)) {
            if (!@mkdir($reportFolder, 0777, true)) {
                throw new \Exception('Could not create Report directory "' . $reportFolder . '", please check the permission.');
            }
        }

        $tempFolder = config('report.temp_folder');

        if (!@is_dir($tempFolder)) {
            if (!@mkdir($tempFolder, 0777, true)) {
                throw new \Exception('Could not create Temp directory "' . $tempFolder . '", please check the permission.');
            }
        }
    }

    /**
     * Update report model
     *
     * @param array $attributes
     * @return bool
     */
    protected function updateModel(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->model->$key = $value;
        }

        return $this->model->save();
    }

    /**
     * Get Message status builder
     *
     * @return \Illuminate\Database\Eloquent\Builder;
     */
    protected function getBuilder()
    {
        return $this->messageBuilder ?:
                $this->messageBuilder = $this->model->getReportBuilder();
    }

    /**
     * Get total Message status data
     *
     * @return int
     */
    public function getTotalData()
    {
        return $this->model->getTotalData();
    }

    /**
     * Get Report Header
     *
     * @return array
     */
    protected function getHeader()
    {
        return [
            'message_id' => 'MESSAGE ID',
            'destination' => 'DESTINATION',
            'op_id' => 'OPERATOR',
            'op_country_code' => 'COUNTRY CODE',
            'message_content' => 'MESSAGE CONTENT',
            'message_count' => 'MESSAGE COUNT',
            'description_status' => 'MESSAGE STATUS',
            'send_datetime' => 'SEND DATETIME',
            'receive_datetime' => 'RECEIVE DATETIME',
            'sender' => 'SENDER',
            'user_id' => 'USER ID'
        ];
    }

    /**
     * Get Message keys that should print to report
     *
     * @return array
     */
    protected function getKeys()
    {
        return $this->keys ?: array_keys($this->getHeader());
    }
}
