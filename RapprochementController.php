<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Rapprochement;
use App\Models\Template;
use App\Models\Abonnement;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;


class RapprochementController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $entreprises= $user->entreprises()->get();
        return view('user.rapprochement', ['entreprises' => $entreprises]);
    }

    public function createAndStore(Request $request)
    {
        $userAbonnement = Abonnement::where('user_id', auth()->user()->id)
        ->where('status', 'complete')
        ->where('date_fin', '>', now())
        ->first();
        dd($userAbonnement);
        $facture = isset($request->journal) ? json_decode($request->journal,true) : [];
        $releve = isset($request->releve) ? json_decode($request->releve,true) : [];
        $data1 = is_array($facture)?$this->objectToArray($facture,"journal"):[];
        $data2 = is_array($releve)?$this->objectToArray($releve,"releve"):[];
        $facture=$this->filterUndefinedLettrage($facture);
        $releve=$this->filterUndefinedLettrage($releve);
        $data3 = is_array($facture)?$this->objectToArray($facture,"journal"):[];
        $data4 = is_array($releve)?$this->objectToArray($releve,"releve"):[];
        $date=$request->date;
        $entreprise_id=$request->entreprise;
        $entreprise=Entreprise::find($entreprise_id);
        $name=$entreprise?$entreprise->nom:"";
        $path = storage_path('app/excels/' . $name . '.xlsx');
        $directoryPath = storage_path('app/excels');
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $spreadsheet = new Spreadsheet();
        $firstsheet = $spreadsheet->getActiveSheet();
        $this->setupSheet($firstsheet,"Lettrage",$data1,$data2,$userAbonnement);
        $secondSheet = $spreadsheet->createSheet();
        $this->setupSheet($secondSheet,"Rapprochement",$data3,$data4,$userAbonnement);
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $excelFile = new Rapprochement();
        $excelFile->nom = $name;
        $excelFile->date_rapprochement = Carbon::parse($date)->endOfMonth();
        $excelFile->fichier_excel = file_get_contents($path);
        $excelFile->save();
        unlink($path);
        return response()->json(['success' => true]);

    }
    function setupSheet($sheet,$title,$data1,$data2,$userAbonnement){
        $row = 2;
        $compteur=0;
        $limit=count($data1);
        if(!$userAbonnement)
        {
            $limit=ceil(count($data1)/10);
        }
        foreach ($data1 as $item) {
            $sheet->fromArray($item, NULL, "A$row");
            $sheet->getStyle("I$row:I$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D3D3D3'],
                ],
            ]);
            $row++;
            $compteur++;
            if($compteur>=$limit+1)
                break;
        }
        $row = 2;
        $compteur=0;
        $limit=count($data2);
        if(!$userAbonnement)
        {
            $limit=ceil(count($data1)/10);
        }
        foreach ($data2 as $item) {
            $sheet->fromArray($item, NULL, "J$row");
            $row++;
            $compteur++;
            if($compteur>=$limit+1)
                break;
        }
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFFF00', // Yellow color
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Grand Livre Bancaire');
        $sheet->mergeCells('J1:O1');
        $sheet->setCellValue('J1', 'Releve Bancaire');
        $sheet->getStyle('A1:H1')->applyFromArray($styleArray);
        $sheet->getStyle('J1:O1')->applyFromArray($styleArray);
        $sheet->setTitle($title);
    }
    function filterUndefinedLettrage($array) {
        return array_filter($array, function($item) {
            return !isset($item['lettrage']) || empty($item['lettrage']);
        });
    }
    private function objectToArray($objects,$source)
    {
        if (empty($objects)) {
            return [];
        }
        if($source=="journal")
            $headers = [
                "Num compte",
                "Date",
                "N° Pièce",
                "Libellé Ecriture",
                "Débit",
                "Crédit",
                "Solde",
                "Lettrage"
            ];
        else
            $headers= [
                "Date",
                "Opération",
                "Débit",
                "Crédit",
                "Solde",
                "Lettrage"
            ];
        $dataArray = [$headers];

        foreach ($objects as $object) {
            $dataArray[] = array_values((array) $object);
        }
        return $dataArray;
    }
    public function historique(Request $request)
    {
        $rapprochements = Rapprochement::orderBy('created_at', 'desc');
        if($request->name)
            $rapprochements=$rapprochements->where("nom","like","%".$request->name."%");
        if($request->date&&strtotime($request->date))
            {
                $date=Carbon::parse($request->date)->endOfMonth();
                $date=$date->format('Y-m-d');
                $rapprochements=$rapprochements->where("date_rapprochement",$date);
            }
        $rapprochements=$rapprochements->paginate(10);
        if ($request->ajax()) {
            return view('user.historiquepartial', compact('rapprochements'))->render();
        }
        return view('user.historiquerapprochement', ['rapprochements' => $rapprochements]);
    }
    public function download($id)
    {
        $rapprochement = Rapprochement::find($id);
        if (!$rapprochement) {
            return response()->json(['error' => 'The requested resource was not found.'], 404);
        }
        $fileContent = $rapprochement->fichier_excel;
        $name=$rapprochement->nom."_Rapprochement_".$rapprochement->date_rapprochement.'.xlsx';
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=" . $name
        ];
            return response()->make($fileContent, 200, $headers);
    }

    public function downloadTemplate($name)
    {
        $template = Template::where("nom","like","%".$name."%")->first();
        if (!$template) {
            return response()->json(['error' => 'The requested resource was not found.'], 404);
        }
        $fileContent = $template->fichier_excel;
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $template->nom . '.xlsx"',
        ];
            return response()->make($fileContent, 200, $headers);
    }
    public function lastSubmission(Request $request)
    {
        $date = Carbon::parse($request->date)->subMonths(1)->endOfMonth()->format("Y-m-d");
        $entreprise=Entreprise::find($request->entreprise);
        $name=$entreprise?$entreprise->nom:"";
        $template1 = Template::where("nom","like","%releve%")->first();
        if(!$template1)
            return response()->json(["error"=>"Template du releve bancaire est introuvable!"]);
        $template2 = Template::where("nom","like","%livre%")->first();
        if(!$template2)
            return response()->json(["error"=>"Template du grand livre bancaire est introuvable!"]);
        $rapprochement= Rapprochement::where("nom",$name)->where("date_rapprochement",$date)->orderBy('created_at', 'desc')->first();
        if(!$rapprochement)
            return response()->json(["data"=>[],"releve"=>base64_encode($template1->fichier_excel),"journal"=>base64_encode($template2->fichier_excel)]);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        file_put_contents($tempFilePath, $rapprochement->fichier_excel);
        $dataArray = [];
        try {
            $spreadsheet = IOFactory::load($tempFilePath);
            $worksheet = $spreadsheet->getSheet(1); // Index 1 for the second sheet

            foreach ($worksheet->getRowIterator(2) as $row) {
                $rowData = [
                    'date' => null,
                    'debit' => 0,
                    'credit' => 0
                ];

                foreach ($row->getCellIterator() as $cell) {
                    $columnIndex = $cell->getColumn();
                    $cellValue = $cell->getValue();
                    if ($columnIndex == 'B') {
                        $rowData['date'] = $cellValue;
                    }
                    else if ($columnIndex == 'E') {
                        if (is_numeric($cellValue)) {
                            $rowData['debit'] = $cellValue;
                        }
                    } elseif ($columnIndex == 'F') {
                        if (is_numeric($cellValue)) {
                            $rowData['credit'] = $cellValue;
                        }
                    }
                }
                if($rowData["debit"]!=0 || $rowData["credit"]!=0 )
                    $dataArray[]=$rowData;
            }
        } finally {
            unlink($tempFilePath);
        }
        return response()->json(["data"=>$dataArray,"releve"=>base64_encode($template1->fichier_excel),"journal"=>base64_encode($template2->fichier_excel)]);
    }
}

