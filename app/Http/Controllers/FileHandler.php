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

        $this->getTable($inputFile);
        $this->toXlsx("$file.csv");

        return null;
    }

    private function getTable($inputFile) : ?bool
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        for($i=0;$i<count($inputFile);$i++) {

            $spreadsheet = $reader->load($inputFile[$i]);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->save($inputFile[$i].".csv");

        }
        $filearr  = Storage::files('excel');
        $filecsv = [];
        foreach ($filearr as $File){
            if(str_contains($File, '.csv')){
                $filecsv[] = $File;
            }
        }
        $arr = [];
        $rowarr =[];
        for ($i=0;$i<count($filecsv);$i++) {

            $readercsv = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $realfile = Storage::path($filecsv[$i]);
            $readercsv->setDelimiter(';');
            $readercsv->setEnclosure('"');
            $encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($realfile);
            $readercsv->setInputEncoding($encoding);

            $spreadsheet = $readercsv->load($realfile);
            $arr[explode('/',$filecsv[$i])[1]]= $spreadsheet;
            $rowarr[explode('/',$filecsv[$i])[1]]= '';
        }


        foreach ($arr as $ar){
            foreach ($rowarr as &$row){
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($ar);
                            $writer->setDelimiter(';');
                            $row= $ar->getActiveSheet()->toArray();
            }

        }

        $arr1 = [];
        $firstiter = true;
        foreach ($rowarr as $key=> $row1){

            foreach ($row1 as $row2){
                if(in_array('Дата подписки',$row2)) {
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
        $arr[array_key_first($arr)]->getActiveSheet()->fromArray($arr1);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($arr[array_key_first($arr)]);
        $writer->setDelimiter(';');
        $writer->setSheetIndex(0);
        $writer->save(explode('.',$inputFile[0])[0].".csv");

        return true;
    }
    private function toXlsx($inputFile) {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();

        $file = explode('.', $inputFile)[0];
        $realfile = Storage::path('/excel/'.$inputFile);

        $reader->setDelimiter(';');
        $reader->setEnclosure('"');
        $encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($realfile);
        $reader->setInputEncoding($encoding);

        $spreadsheet = $reader->load($realfile);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save(Storage::path('excel/').$file.'f.xlsx');

        return null;

    }
}
