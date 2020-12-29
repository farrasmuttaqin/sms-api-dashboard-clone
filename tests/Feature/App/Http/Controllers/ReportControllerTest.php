<?php

namespace Tests\Feature\App\Http\Controllers;

use Carbon\Carbon;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Http\Controllers\ReportController;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ReportRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test Visit Report Page if user doesn't have privilege
     *
     * @return void
     */
    public function test_visit_report_page_if_user_doesnt_have_privilege()
    {
        $uri = route('report.index');

        /**
         * Test Visit Report Page without login first
         */
        $response = $this->get($uri);
        //Should redirect to login page
        $response->assertRedirect(route('auth.login'));

        /**
         * Test authenticate user but It doesn't have any role
         */
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->get($uri);
        $response->assertRedirect(url('/'));
        $response->assertSessionHasErrors('unauthorized');
    }

    /**
     * Test Visit Report Page if user have privilege
     *
     * @return void
     */
    public function test_visit_report_page_if_user_have_privilege()
    {
        $uri = route('report.index');

        /**
         * Test authenticate user for user Super admin
         */
        $user = $this->initializeUserLogin('Super Admin');
        $response = $this->actingAs($user)->get($uri);
        $response->assertViewIs('reports.index');

        /**
         * Test authenticate user for user Company admin
         */
        $user = $this->initializeUserLogin('Admin');
        $response = $this->actingAs($user)->get($uri);
        $response->assertViewIs('reports.index');

        /**
         * Test authenticate user for user Report
         */
        $user = $this->initializeUserLogin('Report');
        $response = $this->actingAs($user)->get($uri);
        $response->assertViewIs('reports.index');
    }

    /**
     * Test Get data for tabel if the request is not ajax
     *
     * @return void
     */
    public function test_get_table_request_if_request_is_not_ajax()
    {
        $uri = route('report.table');

        $user = $this->initializeUserLogin('Super Admin');
        $response = $this->actingAs($user)->get($uri);
        //redirect to page not found
        $response->assertStatus(404);
    }

    /**
     * Test Get data for tabel if the request is ajax but dont have privilege
     *
     * @return void
     */
    public function test_get_table_request_if_request_is_ajax_but_dont_have_privilege()
    {
        $uri = route('report.table');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get($uri, $header);
        $response->assertStatus(403);
        $response->assertJsonStructure(['errors']);
    }

    /**
     * Test Get data for tabel if the request is ajax
     *
     * @return void
     */
    public function test_get_table_request_if_request_is_ajax_and_have_privileges()
    {
        $uri = route('report.table');
        $reports = factory(Report::class, 5)->create();
        $header = ['X-Requested-With' => 'XMLHttpRequest'];

        //Test if user authenticated is Super Admin
        $userLoggin = $this->initializeUserLogin('Super Admin');
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 5]);

        //Test if user authenticated is Company Admin
        $userLoggin = $this->initializeUserLogin('Company Admin');
        $users = factory(User::class, 3)->create([
            'client_id' => $userLoggin->client_id
        ]);
        $reports->random(3)->map(function($report) use (&$users) {
            $report->created_by = $users->random()->getKey();
        });
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 3]);

        //Test if user authenticated have Report Role
        $userLoggin = $this->initializeUserLogin('Report');
        $reports->random(2)->map(function($report) use (&$userLoggin) {
            $report->created_by = $userLoggin->getKey();
        });
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 2]);
    }

    /**
     * Test if get table with some query searching
     *
     * @return void
     */
    public function test_get_table_with_some_query_searching()
    {
        $reports = factory(Report::class, 5)->create();
        $header = ['X-Requested-With' => 'XMLHttpRequest'];

        // Test if user authenticated is Report User
        $userLoggin = $this->initializeUserLogin('Report');
        $reports->random(2)->map(function(&$item, $key) use ($userLoggin) {
            $item->report_name = "Unit Test Report Name " . $key;
            $item->message_status = "sent,delivered,undelivered";
            $item->created_by = $userLoggin->getKey();
            $item->file_type = "csv";
            $item->apiUsers()->sync($userLoggin->apiUsers->pluck('user_id')->all());
            $item->save();
        });

        //Test Search with report_name query
        $uri = route('report.table', ['report_name' => 'Unit Test Report Name']);
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 2]);
        //Test Search with message_status query
        $uri = route('report.table', ['message_status' => 'sent', 'report_name' => 'Unit Test Report Name']);
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 2]);
        //Test Search with client_id query
        $uri = route('report.table', ['client_id' => $userLoggin->client_id, 'report_name' => 'Unit Test Report Name']);
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 2]);
        //Test Search with api_user query
        $uri = route('report.table', ['api_user' => $userLoggin->apiUsers->random()->user_name, 'report_name' => 'Unit Test Report Name']);
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 2]);
        //Test Search with file_type query
        $uri = route('report.table', ['file_type' => 'csv', 'report_name' => 'Unit Test Report Name']);
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertStatus(200);
        $response->assertJsonStructure(['total'], ['total' => 2]);
    }

    /**
     * Test get onProgess report
     *
     * @return void
     */
    public function test_get_on_processing_report()
    {
        $userLoggin = $this->initializeUserLogin('Report');
        $report = factory(Report::class)->create([
            'generate_status' => Report::REPORT_PROCESS,
            'created_by' => $userLoggin->getKey(),
            'pid' => getmypid(),
            'file_path' => "zzzzz.zip",
        ]);
        $path = config('report.temp_folder').'/xxxxx';
        mkdir($path);
        $report->updateManifest(['pid' => -12, 'fileName' => 'xxxxx.zip','temp' => $path]);
        $uri = route('report.processing');
        $response = $this->actingAs($userLoggin)->get($uri);
        $response->assertStatus(200);

        $report = factory(Report::class)->create([
            'generate_status' => Report::REPORT_PROCESS,
            'created_by' => $userLoggin->getKey(),
            'pid' => getmypid(),
            'file_path' => "zzzzz.zip",
        ]);
        $path = config('report.temp_folder').'/xxxxx.zip';
        file_put_contents($path, 'test data');
        $report->updateManifest(['pid' => -12, 'fileName' => 'xxxxx.zip','temp' => $path]);
        $uri = route('report.processing');
        $response = $this->actingAs($userLoggin)->get($uri);
        $response->assertStatus(200);
    }

    /**
     * Test cancel generate report
     *
     * @return  void
     */
    public function test_cancel_generate_report()
    {
        $userLoggin = $this->initializeUserLogin('Report');
        $report = factory(Report::class)->create([
            'generate_status' => Report::REPORT_PROCESS,
            'created_by' => $userLoggin->getKey(),
        ]);
        $uri = route('report.cancel', ['report_id' => $report->getKey()]);
        $response = $this->actingAs($userLoggin)->get($uri);
        $response->assertStatus(200);
        $report->refresh();
        $this->assertEquals($report->getManifest('status'), Report::REPORT_CANCELED);
        $response = $this->actingAs($userLoggin)->get($uri);
        $response->assertStatus(404);
    }

    /**
     * Test Visit Generate Report Page if user doesn't have privilege
     *
     * @return void
     */
    public function test_visit_generate_report_page_if_user_doesnt_have_privilege()
    {
        $uri = route('report.create');

        /**
         * Test Visit Report Page without login first
         */
        $response = $this->get($uri);
        // Should redirect to login page
        $response->assertRedirect(route('auth.login'));

        /**
         * Test authenticate user but It doesn't have any role
         */
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->get($uri);
        $response->assertRedirect(url('/'));
        $response->assertSessionHasErrors('unauthorized');
    }

    /**
     * Test Visit Generate Report Page if user have privilege
     *
     * @return void
     */
    public function test_visit_generate_report_page_if_user_have_privilege()
    {
        $uri = route('report.create');

        /**
         * Test authenticate user for user Super admin
         */
        $user = $this->initializeUserLogin('Super Admin');
        $response = $this->actingAs($user)->get($uri);
        $response->assertViewIs('reports.form');

        /**
         * Test authenticate user for user Company admin
         */
        $user = $this->initializeUserLogin('Admin');
        $response = $this->actingAs($user)->get($uri);
        $response->assertViewIs('reports.form');

        /**
         * Test authenticate user for user Report
         */
        $user = $this->initializeUserLogin('Report');
        $response = $this->actingAs($user)->get($uri);
        $response->assertViewIs('reports.form');
    }

    /**
     * Test Post Generate Report if user doesn't have privilege
     *
     * @return void
     */
    public function test_store_generate_report_if_user_doesnt_have_privilege()
    {
        $uri = route('report.create');

        /**
         * Test Post Generate Report without login first
         */
        $response = $this->post($uri, []);
        //Should redirect to login page
        $response->assertRedirect(route('auth.login'));


        /**
         * Test authenticate user but It doesn't have any role
         */
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->post($uri, []);
        $response->assertRedirect(url('/'));
        $response->assertSessionHasErrors('unauthorized');
    }

    /**
     * Test if Post Generate Report with empty request
     *
     * @return void
     */
    public function test_store_generate_report_with_empty_request()
    {
        $uri = route('report.create');

        $user = $this->initializeUserLogin('Super Admin');
        $response = $this->actingAs($user)->post($uri, []);
        $response->assertSessionHasErrors(['file_type', 'start_date', 'end_date', 'api_users']);
    }

    /**
     * Test if Post Generate Report with correct request
     *
     * @return array
     */
    public function test_store_generate_report_with_correct_request()
    {
        Queue::fake();
        $uri = route('report.create');
        $user = $this->initializeUserLogin('Report');
        $apiUsers = $user->apiUsers
                    ->random(2);
        $request = [
            'report_name' => 'test_store_generate_report',
            'file_type' => 'xlsx',
            'message_status' => 'sent,delivered,undelivered',
            'start_date' => Carbon::parse('-89 days')->format('Y-m-d H:i:s'),
            'end_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'api_users' => $apiUsers->pluck('user_id')->implode(',')
        ];

        factory(\Firstwap\SmsApiDashboard\Entities\Message::class,10)->create([
            'user_id' => $apiUsers->first()->user_name,
            'user_id_number' => $apiUsers->first()->getKey(),
        ]);

        $response = $this->actingAs($user)->post($uri, $request);
        $response->assertRedirect(route('report.index'));

        return $request;
    }

    /**
     * Test if Post Generate Report with incorrect request
     *
     * @return void
     */
    public function test_store_generate_report_with_incorrect_request()
    {
        Queue::fake();
        $uri = route('report.create');
        $user = $this->initializeUserLogin('Report');
        $request = [
            'report_name' => '',
            'file_type' => 'xlsx',
            'message_status' => 'sent,delivered,undelivered',
            'start_date' => Carbon::parse('-91 days')->format('Y-m-d H:i:s'),
            'end_date' => Carbon::parse('-92 days')->format('Y-m-d H:i:s'),
            'api_users' => $user->apiUsers
                    ->pluck('user_id')
                    ->random(2)
                    ->implode(',')
        ];

        $response = $this->actingAs($user)->post($uri, $request);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['no_data']);
    }

    /**
     * Test if Post Generate Report if failed to save
     *
     * @return void
     */
    public function test_store_generate_report_if_failed_save()
    {
        Queue::fake();
        $uri = route('report.create');
        $user = $this->initializeUserLogin('Report');
        $repo = $this->getMockBuilder(ReportRepository::class)
                ->setMethods(['save'])
                ->getMock();
        $repo->expects($this->once())->method('save')->willReturn(false);
        $controller = new ReportController($repo);

        $parameters = [
            'report_name' => '',
            'file_type' => 'xlsx',
            'message_status' => 'sent,delivered,undelivered',
            'start_date' => Carbon::parse('-91 days')->format('Y-m-d H:i:s'),
            'end_date' => Carbon::parse('-92 days')->format('Y-m-d H:i:s'),
            'api_users' => $user->apiUsers
                    ->pluck('user_id')
                    ->random(2)
                    ->implode(',')
        ];
        auth()->login($user);
        $request = Request::create($uri, 'POST', $parameters);
        $response = $controller->store($request);
        $errors = session('errors');

        $this->assertNotEmpty($errors);
        $this->assertEquals($errors->first('failed_save'), trans('validation.failed_save', ['name' => 'report']));
    }

    /**
     * Test Delete Report with correct parameter
     *
     * @return void
     */
    public function test_delete_report_with_correct_parameter()
    {
        $input = $this->test_store_generate_report_with_correct_request();
        $userLoggin = $this->initializeUserLogin('Admin');

        $report = Report::where('report_name', $input['report_name'])->first();
        $uri = route('report.delete', ['report' => $report->getKey()]);
        $report->file_path = 'file_report.xlsx';
        $report->save();
        $fakeFile = UploadedFile::fake()->create('file_report.xlsx', 100);
        Storage::disk('report')->putFileAs('', $fakeFile, 'file_report.xlsx');

        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->delete($uri, [], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test Download Report with correct parameter
     *
     * @return void
     */
    public function test_download_report_with_correct_parameter()
    {
        $input = $this->test_store_generate_report_with_correct_request();
        $userLoggin = $this->initializeUserLogin('Admin');

        $report = Report::where('report_name', $input['report_name'])->first();
        $uri = route('report.download', ['report' => $report->getKey()]);
        $report->file_path = 'file_report.xlsx';
        $report->save();
        $fakeFile = UploadedFile::fake()->create('file_report.xlsx', 100);
        Storage::disk('report')->putFileAs('', $fakeFile, 'file_report.xlsx');
        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->get($uri);

        $response->assertStatus(200);

        Storage::disk('report')->delete('file_report.xlsx');
    }

    /**
     * Test Download Report with report id not found
     *
     * @return void
     */
    public function test_download_report_with_report_id_notfound()
    {
        $userLoggin = $this->initializeUserLogin('Admin');

        $uri = route('report.download', ['report' => 0]);
        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->get($uri);

        $response->assertRedirect(route('report.index'));
        $response->assertSessionHasErrors('notfound');
    }

    /**
     * Test Download Report with file not found
     *
     * @return void
     */
    public function test_download_report_if_file_not_found()
    {
        $input = $this->test_store_generate_report_with_correct_request();
        $userLoggin = $this->initializeUserLogin('Admin');

        $report = Report::where('report_name', $input['report_name'])->first();
        $uri = route('report.download', ['report' => $report->getKey()]);
        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->get($uri);

        $response->assertRedirect(route('report.index'));
        $response->assertSessionHasErrors('no_file');
    }

    /**
     * Test Regenerage report file
     *
     * @return void
     */
    public function test_regenerate_report_file()
    {
        $input = $this->test_store_generate_report_with_correct_request();
        $userLoggin = $this->initializeUserLogin('Admin');
        $report = Report::where('report_name', $input['report_name'])->first();
        $uri = route('report.regenerate', ['report' => $report->getKey()]);
        $response = $this->withoutMiddleware()
            ->actingAs($userLoggin)
            ->get($uri);
        $response->assertRedirect(route('report.index'));
        $response->assertSessionHas('alert-success');
    }

    /**
     * Test Regenerage report file if report id not found
     *
     * @return void
     */
    public function test_regenerate_report_file_if_report_id_not_found()
    {
        $userLoggin = $this->initializeUserLogin('Admin');
        $uri = route('report.regenerate', ['report' => 0]);
        $response = $this->withoutMiddleware()
            ->actingAs($userLoggin)
            ->get($uri);
        $response->assertRedirect(route('report.index'));
        $response->assertSessionHasErrors('notfound');
    }

    /**
     * Test Regenerage report file if report already finished
     *
     * @return void
     */
    public function test_regenerate_report_file_if_report_already_finished()
    {
        $input = $this->test_store_generate_report_with_correct_request();
        $userLoggin = $this->initializeUserLogin('Admin');
        $report = Report::where('report_name', $input['report_name'])->first();
        $report->generate_status = 2;
        $report->save();
        $uri = route('report.regenerate', ['report' => $report->getKey()]);
        $response = $this->withoutMiddleware()
            ->actingAs($userLoggin)
            ->get($uri);
        $response->assertRedirect(route('report.index'));
        $response->assertSessionHasErrors('finished');
    }

}
