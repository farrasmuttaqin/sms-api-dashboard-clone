<?php

namespace Firstwap\SmsApiDashboard\Libraries\Report;

use Box\Spout\Writer\Common\Sheet;
use Box\Spout\Writer\XLSX\Internal\Workbook;
use Firstwap\SmsApiDashboard\Libraries\Report\XLSXWorksheet;

class XLSXWorkbook extends Workbook
{
    /**
     * Creates a new sheet in the workbook. The current sheet remains unchanged.
     *
     * @return Worksheet The created sheet
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the sheet for writing
     */
    public function addNewSheet()
    {
        $newSheetIndex = count($this->worksheets);
        $sheet = new Sheet($newSheetIndex, $this->internalId);

        $worksheetFilesFolder = $this->fileSystemHelper->getXlWorksheetsFolder();
        $worksheet = new XLSXWorksheet($sheet, $worksheetFilesFolder, $this->sharedStringsHelper, $this->styleHelper, $this->shouldUseInlineStrings);
        $this->worksheets[] = $worksheet;

        return $worksheet;
    }

    /**
     * Get fileSystemHelper instance
     *
     * @return  \Box\Spout\Writer\XLSX\Helper\FileSystemHelper
     */
    public function getFileSystemHelper()
    {
        return $this->fileSystemHelper;
    }
}
