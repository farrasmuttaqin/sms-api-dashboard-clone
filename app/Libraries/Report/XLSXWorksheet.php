<?php

namespace Firstwap\SmsApiDashboard\Libraries\Report;

use Box\Spout\Writer\XLSX\Internal\Worksheet;

class XLSXWorksheet extends Worksheet
{
    /**
     * Prepares the worksheet to accept data
     *
     * @return void
     * @throws \Box\Spout\Common\Exception\IOException If the sheet data file cannot be opened for writing
     */
    protected function startSheet()
    {
        $this->sheetFilePointer = fopen($this->worksheetFilePath, 'w');
        $this->throwIfSheetFilePointerIsNotAvailable();

        fwrite($this->sheetFilePointer, self::SHEET_XML_FILE_HEADER);
        fwrite(
            $this->sheetFilePointer,
            '<cols>
                <col min="1" max="1" width="34" customWidth="1"/>
                <col min="2" max="2" width="20" customWidth="1"/>
                <col min="3" max="3" width="40" customWidth="1"/>
                <col min="4" max="10" width="20" customWidth="1"/>
            </cols>'
        );
        fwrite($this->sheetFilePointer, '<sheetData>');
    }
}
