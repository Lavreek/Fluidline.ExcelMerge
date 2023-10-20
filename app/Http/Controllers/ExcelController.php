<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{

    protected function convert($fileName)
    {
        $inputFile = [];
        for ($i = 0; $i <= count($fileName) - 1; $i++) {
            if (file_exists(Storage::path($fileName[$i]))) {
                $inputFile[] = Storage::path($fileName[$i]);
            } else {
                return $fileName;
            }
        }
        $file = explode('.', Storage::path($fileName[0]))[0];
        $this->getTable($inputFile, $file);
        $this->getTable(["$file.csv"], $file);
    }
    protected function getTable($inputFile ,$file){
        $num = 0;
        $bool = false;
        $outputFile ='';
        if(str_contains($inputFile[0], '.csv')){
            $name = explode('/',$file);
            $realName = $name[count($name)-1];
            Storage::putFileAs('excel', $inputFile[0] , $realName."f".".xlsx");
            return true;
        }else{
            $outputFile = fopen("$file.csv", 'c+');
        }
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
    }

    public function download(Request $req)
    {
        $value = $req->input('file');
        return Storage::download($value);

    }
    public function upload(Request $req)
    {


        $files = Storage::Files('/excel');
        $fileName = $req->file('file');
        if($fileName == null){
            return view('welcome', ['files' => $files])->withErrors(['error' => 'Вы ничего не отправили']);
        }
        $path = [];


        for ($i = 0; $i <= count($fileName) - 1; $i++) {
            if (str_contains($fileName[$i]->getClientOriginalName(), '.xlsx')) {
                $path[] = Storage::putFileAs('exсel', $req->file('file')[$i], $fileName[$i]->getClientOriginalName());
            } else {
                return view('welcome', ['files' => $files])->withErrors(['error' => 'Файлы не xlsx']);
            }
        }
        if ($path) {
            $this->convert($path);
        } else {
            return view('welcome', ['files' => $files])->withErrors(['error' => 'ошибка при сохранении файла']);
        }
        $megafiles = Storage::Files('/excel');
        for($i = 0;$i <=count($megafiles)-1;$i++){
            if(!str_contains($megafiles[$i], 'f.xlsx')) {
                Storage::delete($megafiles[$i]);
            }
        }
        $realfiles = Storage::Files('/excel');
        return view('welcome', ['files' => $realfiles])->with('success', 'файл загружен успешно');

    }

}
