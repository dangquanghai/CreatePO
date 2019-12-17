<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Validator;
use DB;
use Carbon\Carbon;

class sal_loiController extends Controller
{
    public $CellValue3=0;
    public $cellValue5N = 0;
    public $cellValue6N = 0;
    private $CurrentDate;

    public function index()
    {
       return view('layouts.PUImport');
     
    }
    public function puImport(Request $request) 
    {
        $validator = Validator::make($request->all(),[
            'file'=>'required|max:5000|mimes:xlsx,xls,csv'
            ]);
        if($validator->passes()){
           $file = $request->file('file');
           $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

           // Import sheet thu nhat
           $reader->setLoadSheetsOnly(["LOI", "sal_loi"]);
           $spreadsheet = $reader->load($file);
           $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();
           DB::table('sal_lois')->delete();
           for($i=2; $i <= $highestRow; $i++){
                $cellValue1 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 1,$i)->getValue();
                $cellValue2 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 2,$i)->getValue();
                $cellValue3 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 3,$i)->getValue();
                $cellValue4 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 4,$i)->getValue();
                DB::table('sal_lois')->insert(
                   ['sku'=> $cellValue1, 'avcwh_level'=>$cellValue2,'fba_level'=>$cellValue3,'y4a_level'=>$cellValue4]
               );
           }

            // Import sheet PU PLAN
            $reader->setLoadSheetsOnly(["PU PLAN", "pu plan"]);
            $spreadsheet = $reader->load($file);
            $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();
            DB::table('pu_plannings')->delete();
            for($i=2; $i <= $highestRow; $i++){
                $cellValue1 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 1,$i)->getValue();
                $cellValue2 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 2,$i)->getValue();
                $this->CellValue3 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 3,$i)->getValue();
                $cellValue4 = $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 4,$i)->getValue();
                $this->CellValue5= $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 6,$i)->getValue();
                $this->CellValue6= $spreadsheet->getActiveSheet()->getCellByColumnAndRow( 7,$i)->getValue();
              
                $cellValue3N = $this->convertToDate($this->CellValue3);
                $cellValue5N = $this->convertToDate($this->CellValue5);
                $cellValue6N = $this->convertToDate($this->CellValue6);


                DB::table('pu_plannings')->insert(
                    ['order_week'=> $cellValue1, 'at_year'=>$cellValue2,'order_date'=>$cellValue3N,'vendor_id'=>$cellValue4,
                    'eta'=>$cellValue5N, 'end_selling_date'=>$cellValue6N]
                );
            }
        }// pass

        return redirect()->back()
        ->with(['success'=>'File Upload successfuly.']);
    }
    
    public function ShowUseContainer() {
        return view('layouts.PUShowContainer');
    }

    public function convertToDate($CellValue) {
        $unix_date = ($CellValue - 25569) * 86400;
        $excel_date = 25569 + ($unix_date / 86400);
        $unix_date = ($CellValue - 25569) * 86400;
        return gmdate("Y-m-d", $unix_date);
    }

}
