<?php

namespace Classes;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductOptionsController extends BaseController
{
    private $file_path = '';

    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }

    public function init()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $offset = 0;
        $limit = 1000;
        $row = 1;
        $table_header = [];
        $product = new OptionsModel();
        while ($products = $product->all($limit * $offset, $limit)) {
            $start = microtime(true);
            // Loop Data Products
            foreach ($products as $index => $info) {
                // Generate Header Column to first row
                // Loop Row Options
                $options = $product->getOptions($info['Id Товара']);
                $index = 0;
                foreach ($options as $group_option) {
                    $row += 1;
                    $column = 0;
                    // Loop Row Product Value
                    foreach ($info as $key => $value) {
                        // Generate Header Column to first row
                        $table_header[$key] = '';
                        $column_name = Coordinate::stringFromColumnIndex($column += 1);
                        $sheet->setCellValue($column_name . ($row), $value . '');
                    }
                    foreach ($group_option as $key => $option_value) {
                        $table_header[$key] = '';
                        $column_name = Coordinate::stringFromColumnIndex($column += 1);
                        $sheet->setCellValue($column_name . ($row), $option_value . '');
                    }
                }
            }

            $offset += 1;
            $format = 'Обработанно %s | Время обработки: %s  сек.';
            echo sprintf($format, ($limit * $offset), round(microtime(true) - $start)) . PHP_EOL;
        }

        $column = 0;
        // Loop Headers Column to first loop
        foreach ($table_header as $key => $value) {
            $column_name = Coordinate::stringFromColumnIndex($column += 1);
            $sheet->setCellValue($column_name . 1, $key . '');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->file_path);
    }
}
