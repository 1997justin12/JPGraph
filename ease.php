<?php
require_once ('jpgraph/src/jpgraph.php');
require_once ('jpgraph/src/jpgraph_scatter.php');
require_once ('jpgraph/src/jpgraph_bar.php');
require('fpdf/fpdf.php');

$command = isset($_GET['year']) ? $_GET['year'] : null;

class PrintInformation{

	private $hostname, $username, $password, $dbname;

	function __construct(){

		$this->hostname = 'localhost';
		$this->username = 'root';
		$this->password = '';
		$this->dbname = 'ease';

	}

	public function connect(){

		$mysqli = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);

		if($mysqli->connect_errno){
			echo "Connection not established";
			return;
		}

		return $mysqli;
	}

	public function printData(){



		$studentData = "select grades.student_id, avg(grades.gwa) as totalGWA, 
						schoolyear.*, eq.* 
						from grades
						left join schoolyear on schoolyear.id = grades.schoolyear
						left join eq on eq.student_id = grades.student_id
						where grades.schoolyear = 1 && schoolyear.semester != 'Summer'
						group by grades.student_id";

		$data = $this->connect()->query($studentData);
		
		$yyAverage = $this->pearsonEquation($data, 'total_eq');
		$xxAverage = $this->pearsonEquation($data, 'totalGWA');
		$xxAverage_yyAverage = $this->pearsonEquation2($xxAverage, $yyAverage);
		$xxAverageSquared = $this->pearsonEquation3($xxAverage);
		$yyAverageSquared = $this->pearsonEquation3($yyAverage);

		$eq = array('intrapersonal', 'interpersonal', 'stress', 'adapt', 'mood');

		for($x = 0; $x<count($eq); $x++){
			$this->scatterPlotGraph($data, $eq[$x]);
			$this->barPlotGraph($data, $eq[$x]);
		}
		$this->getPDF($eq);


	}

	public function pearsonEquation($data, $index){

		$resultData = array();
		$totalEq = 0;

		foreach ($data as $value) {
			$resultData[] = $value[$index];
			$totalEq += $value[$index];
		}

		$totalEq = $totalEq/count($resultData);

		for($x = 0; $x < count($resultData); $x++){
			$resultData[$x] = number_format($resultData[$x] - $totalEq, 5);
		}


		return $resultData;
	}

	public function pearsonEquation2($xxAverage, $yyAverage){

		$xyAverage = array();

		for($x = 0; $x < count($xxAverage); $x++){
			$xyAverage[] = number_format($xxAverage[$x] * $yyAverage[$x], 5);
		}

		return $xyAverage;
	}

	public function pearsonEquation3($data){

		$resultData = array();
		
		for($x = 0; $x < count($data); $x++){
			$resultData[] = number_format(pow($data[$x], 2), 5);
		}

		return $resultData;

	}

	public function scatterPlotGraph($data, $index){

		$arrayGWA = array();
		$arrayEQ = array();

		foreach ($data as  $value) {
			$arrayGWA[] = $value['totalGWA'];
			$arrayEQ[] = $value[$index];
		}

		$titleGraph = "";

		switch($index){
			case 'intrapersonal' :
				$titleGraph = 'Intrapersonal and GWA ScatterPlot';
				break;
			case 'interpersonal' :
				$titleGraph = 'Interpersonal and GWA ScatterPlot';
				break;
			case 'stress' :
				$titleGraph = 'Stress Management and GWA ScatterPlot';
				break;
			case 'adapt' :
				$titleGraph = 'Adaptability and GWA ScatterPlot';
				break;
			case 'mood' :
				$titleGraph = 'General Modd and GWA ScatterPlot'; 
				break;
			default :

				break;

		}


		$graph = new Graph(760,400);
		$graph->SetScale("intlin", 0, 0);
		$graph->xaxis->scale->SetAutoMin(0);
		$graph->yaxis->scale->SetAutoMin(0);
		$graph->yaxis->scale->SetAutoMax(5);

		 
		$graph->img->SetMargin(40,40,40,40);        
		$graph->SetShadow();
		 
		$graph->title->Set($titleGraph.' '.count($arrayEQ));
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		 
		$sp1 = new ScatterPlot($arrayGWA,$arrayEQ);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		 $sp1->mark->SetFillColor("red");
		$graph->Add($sp1);	

		@unlink($index.".png");

		$graph->Stroke($index.'.png');

	}

	public function barPlotGraph($data, $index){
		$barPlot = array();
		$bar = array();
		foreach ($data as $value) {
			if($value[$index] >= 50 && $value[$index] <= 84){
				$barPlot['low'][]= $value[$index];
			}elseif($value[$index] >= 85 && $value[$index] <= 114){
				$barPlot['average'][] = $value[$index];				
			}elseif($value[$index] >= 115 && $value[$index] <= 170){	
				$barPlot['high'][] = $value[$index];
			}	
		}

		 
		// Create the graph. These two calls are always required
		$graph = new Graph(760,400);    
		$graph->SetScale("textlin");
		 
		$graph->SetShadow();
		$graph->img->SetMargin(40,30,20,40);
		 
		// Create the bar plots
		$arrayPlot = array();
		foreach($barPlot as $value){
			$plotData = 0;
			foreach ($value as $points) {
				$plotData += 1;
			}
			$arrayPlot[] = $plotData;						
		}
		// $b1plot->SetFillColor("orange");
		// $b2plot = new BarPlot($data2y);
		// $b2plot->SetFillColor("blue");
		 
		// Create the grouped bar plot
		$plot = new BarPlot($arrayPlot);
		 // $gbplot = new GroupBarPlot($plot);
		 
		// ...and add it to the graPH
		$graph->Add($plot);
		 
		$graph->title->Set("Example 21");
		$graph->xaxis->title->Set("X-title");
		$graph->yaxis->title->Set("Y-title");
		 
		 
		// Display the graph
		@unlink($index."Bar".".png");		
		$graph->Stroke($index.'Bar'.".png");
	}

	public function getPDF($data){
		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);

		$flag = 0;

		while($flag < count($data)){

			$pdf->Cell(40,10, $pdf->Image($data[$flag++].'.png', 5, null));
			$pdf->Ln(12);
		}
		$bar = 0;		
		do{

			$pdf->Cell(40,10, $pdf->Image($data[$bar++].'Bar.png', 5, null));
			$pdf->Ln(12);
			$flag--;
		}while($flag != 0);


		$pdf->Output('D', "Test.pdf");
	}


}

$print = new PrintInformation();
$print->printData();

?>

