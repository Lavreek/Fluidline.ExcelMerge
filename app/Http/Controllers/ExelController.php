<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExelController extends Controller
{
    public function upload(Request $req)
    {
        $fileName = $req->file('file');
        $count = count($fileName) - 1;
        $path = [];


        for ($i = 0; $i <= $count; $i++) {
            if (str_contains($fileName[$i]->getClientOriginalName(), '.xlsx')) {
                $path[] = Storage::putFileAs('exel', $req->file('file')[$i], $fileName[$i]->getClientOriginalName());
            } else {
                return view('welcome')->withErrors(['error' => 'Файлы не xlsx']);
            }
        }
        if ($path) {
             $this->convert($path);
        } else {
            return view('welcome')->withErrors(['error' => 'ошибка при сохранении файла']);
        }
        $files = Storage::Files('/exel');
        for($i = 0;$i <=count($files)-1;$i++){
            if(!str_contains($files[$i], 'f.xlsx')) {
                Storage::delete($files[$i]);
            }
        }
        $realfiles =Storage::Files('/exel');
        return view('welcome', ['files' => $realfiles])->with('success', 'файл загружен успешно');

    }

    /**
     * @throws Exception
     */
    protected function convert($fileName)
    {
        $inputFile = [];
        for ($i = 0; $i <= count($fileName) - 1; $i++) {
            if (file_exists("/home/userq/exelForm/storage/app/$fileName[$i]")) {
                $inputFile[] = "/home/userq/exelForm/storage/app/$fileName[$i]";

            } else {
                return $fileName;
            }
        }
        $file = explode('.', $fileName[0])[0];
        $outputFile = fopen("/home/userq/exelForm/storage/app/$file.csv", 'c+');
        $count = [];
        $num = 0;
        $bool = false;
        for ($i = 0; $i <= count($inputFile) - 1; $i++) {

            $spreadsheet = IOFactory::load($inputFile[$i]);
            $name = explode('/',$inputFile[$i]);
            $realName = $name[count($name)-1];
            $activeSheet = $spreadsheet->getActiveSheet();
            $highestRow = $activeSheet->getHighestDataRow();
            $highestColumn = $activeSheet->getHighestDataColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            for ($row = 1; $row <= $highestRow; ++$row) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $value = $activeSheet->getCellByColumnAndRow($col, $row)->getValue();
                    if($row==1 && $i == 0){
                        $count[] = $value;
                        if (!preg_match('/\w+\.\w+\.\w+/', $value)) {
                            fwrite($outputFile, "\"$value\"" . ';');
                        } else {
                            fwrite($outputFile, PHP_EOL . "\"$value\"" . ';');
                        }
                    }else {


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
                                if($col == 5 && $row != 1){
                                    fwrite($outputFile, "\"$realName\"" .';');
                                }else {


                                    fwrite($outputFile, "\"$value\"" . ';');
                                }
                            } else {
                                fwrite($outputFile, PHP_EOL . "\"$value\"" . ';');
                            }
                        }
                    }
                }
            }
        }
        fclose($outputFile);
        $outputFil1e = fopen("/home/userq/exelForm/storage/app/$file" . "f" . ".xlsx", 'c+');
        $spreadsheetexel = IOFactory::load("/home/userq/exelForm/storage/app/$file.csv");
        $activeSheetex = $spreadsheetexel->getActiveSheet();
        $highestRow = $activeSheetex->getHighestDataRow();
        $highestColumn = $activeSheetex->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($row = 1; $row <= $highestRow; ++$row) {
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $value = $activeSheetex->getCellByColumnAndRow($col, $row)->getValue();
                if (!preg_match('/\w+\.\w+\.\w+/', $value)) {
                    fwrite($outputFil1e, "\"$value\"" . ';');

                }else {
                    fwrite($outputFil1e, PHP_EOL . "\"$value\"".';');
                }
            }
        }

    }

    public function download(Request $req)
    {
        $value = $req->input('file');
        return Storage::download($value);

    }

}
