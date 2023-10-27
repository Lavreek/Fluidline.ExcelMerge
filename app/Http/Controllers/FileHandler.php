<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileHandler
{
    public function convert($filename) : ?array
    {
        $inputFile = [];

        for ($i = 0; $i <= count($filename) - 1; $i++) {
            if (file_exists(Storage::path($filename[$i]))) {
                $inputFile[] = Storage::path($filename[$i]);

            } else {
                return $filename;
            }
        }

        $fileinfo = pathinfo($filename[0]);

        $file = $fileinfo['filename'];

        $this->getTable($inputFile, $file);
        $this->getTable(["$file.csv"], $file);

        return null;
    }

    private function getTable($inputFile, $file) : ?bool
    {
        $num = 0;
        $bool = false;


        if (str_contains($inputFile[0], '.csv')) {
            $outputFile = fopen(Storage::path('excel/').$file.'f.xlsx', 'c+');
            $inputFile[0] = Storage::path('/excel/'.$inputFile[0]);
        } else {
            $outputFile = fopen(Storage::path('excel/').$file.'.csv', 'c+');
        }


        for ($i = 0; $i <= count($inputFile) - 1; $i++) {
            $spreadsheet = IOFactory::load($inputFile[$i]);
            $name = explode('/',$inputFile[$i]);
            $realName = $name[count($name)-1];
            $activeSheet = $spreadsheet->getActiveSheet();
            $highestRow = $activeSheet->getHighestDataRow();
            $highestColumn = $activeSheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            for ($row = 1; $row <= $highestRow; ++$row) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $value = $activeSheet->getCellByColumnAndRow($col, $row)->getValue();

                    if ($row == 1 && $i == 0 or str_contains($inputFile[0], '.csv')) {
                        if (!preg_match('/\w+\.\w+\.\w+/', $value)) {
                            fwrite($outputFile, "\"$value\"" . ';');

                        } else {
                            fwrite($outputFile, PHP_EOL . "\"$value\"" . ';');
                        }
                    } else {
                        if (!$bool) {
                            if (preg_match('/\w+\.\w+\.\w+/', $value)) {
                                if ($i >= 0) {
                                    fwrite($outputFile, PHP_EOL . "\"$value\"" . ';');
                                    $bool = true;
                                }
                            }
                        } else {
                            $num++;

                            if ($num >= 17) {
                                $num = 0;
                                $bool = false;
                            }

                            if (!preg_match('/\w+\.\w+\.\w+/', $value)) {
                                if ($col == 5 && $row != 1){
                                    fwrite($outputFile, "\"$realName\";");

                                } else {
                                    fwrite($outputFile, "\"$value\";");
                                }
                            } else {
                                fwrite($outputFile, PHP_EOL . "\"$value\";");
                            }
                        }
                    }
                }
            }
        }

        fclose($outputFile);

        return true;
    }
}
