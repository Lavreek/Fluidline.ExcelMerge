<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Csv AS ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv as WriterCsv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

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

        $this->getTable($inputFile);
        $this->toXlsx("$file.csv");

        return null;
    }

    private function getTable($inputFile) : ?bool
    {
        $reader = new ReaderXlsx();

        for ($i = 0; $i < count($inputFile); $i++) {
            $fileinfo = pathinfo($inputFile[$i]);

            $spreadsheet = $reader->load($inputFile[$i]);
            $writer = new WriterCsv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->save($fileinfo['dirname'] ."/". $fileinfo['filename'] .".csv");
        }

        $filearr  = Storage::files('excel');
        $filecsv = [];

        foreach ($filearr as $file) {
            $fileinfo = pathinfo($file);

            if (isset($fileinfo['extension'])) {
                if ($fileinfo['extension'] === 'csv') {
                    $filecsv[] = $file;
                }
            }
        }

        $arr = [];
        $rowarr =[];

        for ($i = 0; $i < count($filecsv); $i++) {
            $readercsv = new ReaderCsv();
            $realfile = Storage::path($filecsv[$i]);
            $readercsv->setDelimiter(';');
            $encoding = ReaderCsv::guessEncoding($realfile);
            $readercsv->setInputEncoding($encoding);
            $spreadsheet = $readercsv->load($realfile);

            $fileKey = pathinfo($filecsv[$i]);

            $arr[$fileKey['filename']]= $spreadsheet;
            $rowarr[$fileKey['filename']]= $spreadsheet->getActiveSheet()->toArray();
        }

        $arr1 = [];
        $firstiter = true;

        foreach ($rowarr as $key => $row1) {
            foreach ($row1 as $row2){
                if (in_array('Дата подписки', $row2)) {
                    if ($firstiter) {
                        $arr1[] = $row2;
                        $firstiter = false;
                    }
                    continue;
                }

                $row2[4] = $key;
                $arr1[]= $row2;
            }
        }

        $fileinfo = pathinfo($inputFile[0]);

        $arr[array_key_first($arr)]->getActiveSheet()->fromArray($arr1);
        $writer = new WriterCsv($arr[array_key_first($arr)]);
        $writer->setDelimiter(';');
        $writer->setSheetIndex(0);
        $writer->save($fileinfo['dirname'] ."/". $fileinfo['filename'] .".csv");

        return true;
    }

    private function toXlsx($inputFile) {
        $reader = new ReaderCsv();

        $file = explode('.', $inputFile)[0];
        $realfile = Storage::path('/excel/'.$inputFile);

        $reader->setDelimiter(';');
        $reader->setEnclosure('"');
        $encoding = ReaderCsv::guessEncoding($realfile);
        $reader->setInputEncoding($encoding);

        $spreadsheet = $reader->load($realfile);

        $writer = new WriterXlsx($spreadsheet);
        $writer->save(Storage::path('excel/').$file.'f.xlsx');

        return null;
    }
}
