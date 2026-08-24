<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Builds a real .xlsx workbook that Microsoft Excel can open.
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
        $safeName = mb_substr(trim($safeName), 0, 31) ?: 'Sheet';
        $this->sheets[$safeName] = $rows;

        return $this;
    }

    public function toXlsx(): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP zip extension is required to export Excel files.');
        }

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary Excel file.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new RuntimeException('Unable to write the Excel workbook.');
        }

        $sheetNames = array_keys($this->sheets);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml(count($sheetNames)));
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetNames));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml(count($sheetNames)));

        $index = 1;
        foreach ($this->sheets as $rows) {
            $zip->addFromString("xl/worksheets/sheet{$index}.xml", $this->sheetXml($rows));
            $index++;
        }

        $zip->close();

        $binary = file_get_contents($path);
        @unlink($path);

        if ($binary === false) {
            throw new RuntimeException('Unable to read the generated Excel file.');
        }

        return $binary;
    }

    private function contentTypesXml(int $sheetCount): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .$overrides
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    /**
     * @param  list<string>  $sheetNames
     */
    private function workbookXml(array $sheetNames): string
    {
        $sheets = '';
        foreach ($sheetNames as $index => $name) {
            $sheetId = $index + 1;
            $sheets .= '<sheet name="'.$this->escape($name).'" sheetId="'.$sheetId.'" r:id="rId'.$sheetId.'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$sheets.'</sheets>'
            .'</workbook>';
    }

    private function workbookRelsXml(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$rels
            .'</Relationships>';
    }

    /**
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function sheetXml(array $rows): string
    {
        $sheetData = '';

        foreach (array_values($rows) as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $sheetData .= '<row r="'.$excelRow.'">';

            foreach (array_values($row) as $colIndex => $value) {
                $ref = $this->columnLetter($colIndex).$excelRow;
                $text = $this->escape((string) ($value ?? ''));
                $sheetData .= '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>';
            }

            $sheetData .= '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.$sheetData.'</sheetData>'
            .'</worksheet>';
    }

    private function columnLetter(int $zeroIndex): string
    {
        $letter = '';
        $index = $zeroIndex + 1;

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $index = intdiv($index - 1, 26);
        }

        return $letter;
    }

    private function escape(string $value): string
    {
        $value = preg_replace('/[^\P{C}\t\n\r]/u', '', $value) ?? $value;

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
