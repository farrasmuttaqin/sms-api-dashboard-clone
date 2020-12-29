<?php

namespace Firstwap\SmsApiDashboard\Libraries\Report;

use Box\Spout\Writer\XLSX\Writer;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Firstwap\SmsApiDashboard\Libraries\Report\XLSXWorkbook;

class XLSXWriter extends Writer
{

    /**
     * XLSXWriter construtor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setGlobalFunctionsHelper(new GlobalFunctionsHelper());
    }

    /**
     * Configures the write and sets the current sheet pointer to a new sheet.
     *
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the file for writing
     */
    protected function openWriter()
    {
        if (!$this->book) {
            $tempFolder = ($this->tempFolder) ? : sys_get_temp_dir();
            $this->book = new XLSXWorkbook($tempFolder, $this->shouldUseInlineStrings, $this->shouldCreateNewSheetsAutomatically, $this->defaultRowStyle);
            $this->book->addNewSheetAndMakeItCurrent();
        }
    }

    /**
     * Get workbook instance
     *
     * @return  XLSXWorkbook
     */
    public function getBook()
    {
        return $this->book;
    }
}
