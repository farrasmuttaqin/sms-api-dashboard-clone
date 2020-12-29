<?php

namespace Tests\Unit\App\Libraries\Report;

use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Libraries\Report\WriterReportFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use org\bovigo\vfs\vfsStream;

class WritingReportTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Create Virtual file system
     *
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    protected function initVirtualDirectory()
    {
        $path = vfsStream::setup('reports');
        config(['report.folder' => $path->url()]);
        config(['report.temp_folder' => $path->url() . DIRECTORY_SEPARATOR . 'temp']);

        return $path;
    }

    /**
     * Test Create Folder Report method
     *
     * @return void
     */
    public function test_createFolderReport_method()
    {
        $path = $this->initVirtualDirectory();
        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);
        $tempFolder = config('report.temp_folder');
        $reportFolder = config('report.folder');

        //call createFolderReport method
        $this->invokeMethod($object, 'createFolderReport');
        //Folder Temp should exists
        $this->assertFileExists($tempFolder);
        //Folder report should exists
        $this->assertFileExists($reportFolder);
    }

    /**
     * Test if failed Create Report Folder
     * This file will throw exception
     *
     * @expectedException \Exception
     * @return void
     */
    public function test_createFolderReport_method_if_failed_create_report_folder()
    {
        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);

        $path = vfsStream::setup('root', 000);
        config(['report.folder' => $path->url() . DIRECTORY_SEPARATOR . 'reports']);
        config(['report.temp_folder' => $path->url() . DIRECTORY_SEPARATOR . 'temp']);
        //call createFolderReport method
        $this->invokeMethod($object, 'createFolderReport');
    }

    /**
     * Test if failed Create Report Folder
     * This file will throw exception
     *
     * @expectedException \Exception
     * @return void
     */
    public function test_createFolderReport_method_if_failed_create_temp_folder()
    {
        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);
        $path = $this->initVirtualDirectory();
        $path->chmod(000);
        //call createFolderReport method
        $this->invokeMethod($object, 'createFolderReport');
    }

    /**
     * Test createReportFile method
     *
     * @return void
     */
    public function test_createReportFile_method()
    {
        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);

        //call createFolderReport method
        $this->invokeMethod($object, 'createFolderReport');
        $report->refresh();

        $this->assertFileExists(storage_path('reports/' . $report->fileType));

        Storage::disk('report')->delete($report->file_path);
        $this->invokeMethod($object, 'close');
        $this->assertFileNotExists(storage_path('reports/' . $report->file_path));
    }

    /**
     * Test If generate report file
     *
     * @return void
     */
    public function test_generate_report_file()
    {
        $report = factory(Report::class)->create([
            'message_status' => 'sent'
        ]);
        $messages = factory(Message::class, 100)->create();
        $report->apiUsers()->sync($messages->pluck('user_id_number')->unique()->all());
        $object = new WriterReportFile($report);
        $object->generate();
        $report->refresh();
        $this->assertEquals(Report::REPORT_FINISHED, $report->generate_status);
        $this->assertEquals(1, $report->percentage);
        $this->assertNotEmpty($report->pid);
        $this->assertFileExists(storage_path('reports/' . $report->file_path));
        Storage::disk('report')->delete($report->file_path);
        $this->assertFileNotExists(storage_path('reports/' . $report->file_path));

        $report->update(['file_type' => 'csv']);
        $object = new WriterReportFile($report);
        $object->generate();
        $report->refresh();
        $this->assertEquals(Report::REPORT_FINISHED, $report->generate_status);
        $this->assertEquals(1, $report->percentage);
        $this->assertNotEmpty($report->pid);
        $this->assertFileExists(storage_path('reports/' . $report->file_path));
        Storage::disk('report')->delete($report->file_path);
        $this->assertFileNotExists(storage_path('reports/' . $report->file_path));
    }

    /**
     * Test If generate report file if no data
     *
     * @return void
     */
    public function test_generate_report_file_if_no_data()
    {
        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);
        $object->generate();
        $report->refresh();
        $this->assertEquals(Report::REPORT_FAILED, $report->generate_status);
        $this->assertEquals(0, $report->percentage);
        $this->assertEmpty($report->file_path);
    }

    /**
     * Test if create a report file and than throw error
     *
     * @return void
     */
    public function test_generate_report_file_and_than_throw_error()
    {
        $report = factory(Report::class)->create();
        $object = $this
                ->getMockBuilder(WriterReportFile::class)
                ->setConstructorArgs([$report])
                ->setMethods(['getTotalData'])
                ->getMock();

        $object->expects($this->once())
                ->method('getTotalData')
                ->willThrowException(new \Exception('TESTING test_generate_report_file_if_than_throw_error'));

        $object->generate();

        $report->refresh();
        $this->assertEquals(Report::REPORT_FAILED, $report->generate_status);
    }

    /**
     * Test if create a report file and than throw error when status report is change
     *
     * @return void
     */
    public function test_generate_report_file_and_than_throw_error_when_status_change()
    {
        $report = factory(Report::class)->create();
        config(['report.limit_batch' => 10]);
        $messages = factory(Message::class, 100)->create();
        $report->apiUsers()->sync($messages->pluck('user_id_number')->unique()->all());
        $object = $this
                ->getMockBuilder(WriterReportFile::class)
                ->setConstructorArgs([$report])
                ->setMethods(['addRow'])
                ->getMock();

        $object->expects($this->atLeastOnce())
                ->method('addRow')
                ->willReturnCallback(function($messages) use ($report){
                    $report->updateManifest(['status' => Report::REPORT_CANCELED]);
                });

        $object->generate();

        $report->refresh();
        $this->assertEquals(Report::REPORT_CANCELED, $report->generate_status);
    }


    /**
     * Test Create Zip File when file already exists
     *
     * @return  void
     */
    public function test_createZipFile_method_when_file_already_exists()
    {

        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);

        // call createZipFile method
        $pathFile = $this->invokeMethod($object, 'createZipFile');
        file_put_contents($pathFile, 'test data exists');

        $this->expectException(\Exception::class);
        //call createZipFile method
        $this->invokeMethod($object, 'createZipFile');
    }

    /**
     * Test initReportWriter
     *
     * @return  void
     */
    public function test_initReportWriter_method_when_writer_already_exists()
    {

        $report = factory(Report::class)->create();
        $object = new WriterReportFile($report);

        // call createZipFile method
        $pathFile1 = $this->invokeMethod($object, 'initReportWriter');
        $pathFile2 = $this->invokeMethod($object, 'initReportWriter', [2]);

        $this->assertNotEquals($pathFile1, $pathFile2);
    }

}
