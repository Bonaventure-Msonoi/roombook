<?php

namespace App\Support;

/**
 * Builds a multi-sheet Excel XML workbook (SpreadsheetML).
 * Opens natively in Microsoft Excel without extra PHP extensions.
 */
class ExcelWorkbook
{
    /** @var array<string, list<list<string|int|float|null>>> */
    private array $sheets = [];

    /**
     * @param  list<list<string|int|float|null>>  $rows
     */
    public function addSheet(string $name, array $rows): self
    {
        $safeName = preg_replace('/[\\\\\/\?\*\[\]]+/', ' ', $name) ?: 'Sheet';
        $safeName = mb_substr(trim($safeName), 0, 31);
        $this->sheets[$safeName] = $rows;

        return $this;
    }

    public function toXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'."\n";
        $xml .= '<Styles>'
            .'<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/></Style>'
            .'</Styles>'."\n";

        foreach ($this->sheets as $name => $rows) {
            $xml .= '<Worksheet ss:Name="'.$this->escape($name).'">'."\n";
            $xml .= '<Table>'."\n";

            foreach ($rows as $index => $row) {
                $xml .= '<Row>'."\n";
                foreach ($row as $cell) {
                    $style = $index === 0 ? ' ss:StyleID="Header"' : '';
                    if (is_int($cell) || is_float($cell)) {
                        $xml .= '<Cell'.$style.'><Data ss:Type="Number">'.$cell.'</Data></Cell>'."\n";
                    } else {
                        $xml .= '<Cell'.$style.'><Data ss:Type="String">'.$this->escape((string) ($cell ?? '')).'</Data></Cell>'."\n";
                    }
                }
                $xml .= '</Row>'."\n";
            }

            $xml .= '</Table>'."\n";
            $xml .= '</Worksheet>'."\n";
        }

        $xml .= '</Workbook>';

        return $xml;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
