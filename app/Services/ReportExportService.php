<?php

namespace App\Services;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Stream an XLSX download using OpenSpout.
     *
     * @param  array<int, string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public function streamDownload(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $rows) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $headerStyle = (new Style)
                ->setFontBold()
                ->setFontSize(11)
                ->setFontColor(Color::WHITE)
                ->setBackgroundColor('0D9488'); // Teal-600

            $headerRow = Row::fromValues($headers, $headerStyle);
            $writer->addRow($headerRow);

            $zebra = false;
            $rowStyle1 = (new Style)->setFontSize(10);
            $rowStyle2 = (new Style)->setFontSize(10)->setBackgroundColor('F8FAFC');

            foreach ($rows as $rowData) {
                $style = $zebra ? $rowStyle2 : $rowStyle1;
                $writer->addRow(Row::fromValues($rowData, $style));
                $zebra = ! $zebra;
            }

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
