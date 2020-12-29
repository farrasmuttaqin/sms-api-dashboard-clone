<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Jobs\GenerateReport;
use Firstwap\SmsApiDashboard\Libraries\Report\NoDataToGenerateException;
use Firstwap\SmsApiDashboard\Libraries\Repositories\Repository as RepositoryContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportRepository extends RepositoryContract
{

    use AuthorizesRequests;

    /**
     * Create a new ReportRepository instance.
     *
     * @return void
     */
    function __construct()
    {
        $this->model = $this->model();
    }

    /**
     * Get Model instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        return $this->model ?? new Report;
    }

    /**
     * Get Report Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder()
    {
        $builder = $this->model()->query();

        if ($this->privileges(Privilege::REPORT_ACC_SYSTEM))
        {
            return $builder;
        }

        if ($this->privileges(Privilege::REPORT_ACC_COMPANY))
        {
            return $builder
                    ->whereHas('apiUserDashboard',function($query)
                    {
                        $query->where('client_id', $this->user()->client_id)
                              ->whereDoesntHave('roles', function($query)
                              {
                                  $query->whereHas('privileges', function($query)
                                  {
                                      $query->where('privilege_name', Privilege::REPORT_ACC_SYSTEM);
                                  });
                              });
                    });
        }

        if ($this->privileges(Privilege::REPORT_ACC_OWN))
        {
            return $builder->where('created_by', $this->user()->getKey());
        }

        return $this->deny();
    }

    /**
     * Get model data with pagination format for table
     *
     * @param array $search
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function table(array $search = [])
    {
        $request = request()->all();

        $builder = $this
                ->searchBuilder($search)
                ->with('apiUserDashboard:ad_user_id,name,client_id');

        return tap($this->pagination($builder), function($data)
                {
                    $data->getCollection()
                        ->map(function(Report $report)
                        {
                            $report->append('download_url')
                                ->setVisible(
                                    $this->getVisibleAttribute()
                                );
                        });
            });
    }

    /**
     * Store new or update Report data to database
     *
     * @param   array   $attributes  An array report's attributes that will insert to database
     * @param   Report  $report      Update action will be execute when Report instance exists
     * @return  boolean
     */
    public function save(array $attributes = [], Report $report = null)
    {
        $report = $report ?? $this->model();

        $report->fill($attributes);

        if (empty($report->created_by))
        {
            $report->created_by = $this->user()->getKey();
        }

        DB::connection('api_dashboard')->beginTransaction();

        $saved = $report->save();

        if ($saved && !empty($attributes['api_users']))
        {
            $report->apiUsers()->sync($attributes['api_users']);
            $this->generateReport($report);
        }

        DB::connection('api_dashboard')->commit();

        return $saved;
    }

    /**
     * Dispatch GenerateReport job to start generate report file
     *
     * @param   Report $report
     * @return  void
     */
    public function generateReport(Report $report)
    {
        if ($report->getTotalData() === 0)
        {
            throw new NoDataToGenerateException();
        }

        $report->update([
            'generate_status' => Report::REPORT_QUEUE,
            'percentage' => 0
        ]);

        GenerateReport::dispatch($report, $this->user()->timezone);
    }

    /**
     * Search data with some query
     *
     * @param  array $attributes  An array data from request to filter the data
     * @return Builder
     */
    public function searchBuilder(array $attributes = [])
    {
        $builder = $this->builder();

        if (!empty($attributes['report_name']))
        {
            $builder->where('report_name', 'like', '%' . $attributes['report_name'] . '%');
        }

        if (!empty($attributes['message_status']))
        {
            $builder->where('message_status', 'like', '%' . $attributes['message_status'] . '%');
        }

        if (!empty($attributes['client_id']))
        {
            $clientId = $attributes['client_id'];
            $builder->whereHas('apiUserDashboard', function($query) use ($clientId)
            {
                $query->where('client_id', $clientId);
            });
        }

        if (isset($attributes['api_user']))
        {
            $apiUserId = $attributes['api_user'];
            $builder->whereHas('apiUsers', function($query) use ($apiUserId)
            {
                $query->where('user_id', $apiUserId);
            });
        }

        if (isset($attributes['file_type']))
        {
            $builder->where('file_type', $attributes['file_type']);
        }

        if (isset($attributes['report_id']))
        {
            $builder->where('report_id', $attributes['report_id']);
        }

        return $builder;
    }

    /**
     * Find a data with specific primary key
     *
     * @param  int $reportId
     * @return Firstwap\SmsApiDashboard\Entities\Report|null
     */
    public function find($reportId)
    {
        return $this->searchBuilder(['report_id' => $reportId])->first();
    }

    /**
     * Remove the model from database.
     *
     * @param  int  $reportId
     * @return bool
     */
    public function delete($reportId)
    {
        $report     = $this->find($reportId);
        $deleted    = false;

        if (!is_null($report))
        {
            $this->authorize('delete', $report);

            if ($deleted = $report->delete())
            {
                $this->deleteReportFile($report->file_path);
                Cache::flush($report->cache_key);
            }
        }

        return (bool) $deleted;
    }

    /**
     * Delete Report File
     *
     * @param string $filePath  The report file path location
     * @return bool
     */
    public function deleteReportFile($filePath)
    {
        $deleted = false;

        if (Storage::disk('report')->exists($filePath))
        {
            $deleted = Storage::disk('report')->delete($filePath);
        }

        return $deleted;
    }

    /**
     * Get list of report that still on progress
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function onProcess()
    {
        return $this->builder()
            ->where('generate_status', Report::REPORT_PROCESS)
            ->get();
    }

    /**
     * Change status report that status is progress but pid number does not exist on system
     * It will assuming the report is failed
     *
     * @param   Report $report  Report instance that still on progress generate report
     * @return  void
     */
    public function failedReport(Report $report)
    {
        $report->generate_status = Report::REPORT_FAILED;
        $report->pid             = 0;
        $report->percentage      = 0;
        $this->deleteReportFile($report->file_path);

        if ($manifest = $report->getManifest())
        {
            if (!empty($manifest['temp']))
            {
                $fileSystem     = new Filesystem;
                $deletedTemp    = $fileSystem->deleteDirectory($manifest['temp']);

                if ($deletedTemp === false)
                {
                    array_map('unlink', glob($manifest['temp'] . "*.zip"));
                }
            }

            if (!empty($manifest['fileName']))
            {
                $dirPrefix = config('report.temp_folder')."/".$manifest['fileName']. "*";
                array_map('unlink', glob($dirPrefix));
            }
        }

        $report->save();
    }

    /**
     * Get attribute that should be visible on json
     *
     * @return array
     */
    public function getVisibleAttribute()
    {
        return [
            'report_id',
            'report_name',
            'generated_at',
            'message_status',
            'generate_status',
            'file_type',
            'start_date',
            'end_date',
            'percentage',
            'download_url',
            'apiUserDashboard'
        ];
    }

}
