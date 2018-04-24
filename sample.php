<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/src/jpgraph.php');
require_once ('jpgraph/src/jpgraph_scatter.php');
require('fpdf/fpdf.php');

$datax = array(3.5,3.7,3,4,6.2,6,3.5,8,14,8,11.1,13.7);
$datax1 = array(5,6,7,8,9,10,11,12,14,13,16.1,17);
$datay = array(20,22,12,13,17,20,16,19,30,31,40,43);
 
$graph = new Graph(800,500);
$graph->SetScale("linlin");
 
$graph->img->SetMargin(40,40,40,40);        
$graph->SetShadow();
 
$graph->title->Set("A simple scatter plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
 
$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
 $sp1->mark->SetFillColor("red");
$graph->Add($sp1);


$sp1 = new ScatterPlot($datax1,$datay);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor("pink");
$graph->Add($sp1);
// $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
@unlink("test-image.png");

$graph->Stroke('test-image.png');


$graph = new Graph(300,200);
$graph->SetScale("linlin");
 
$graph->img->SetMargin(40,40,40,40);        
$graph->SetShadow();
 
$graph->title->Set("A simple scatter plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
 
$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
 $sp1->mark->SetFillColor("red");
$graph->Add($sp1);


$sp1 = new ScatterPlot($datax1,$datay);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor("pink");
$graph->Add($sp1);
// $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
@unlink("test-image1.png");

$graph->Stroke('test-image1.png');


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

$pdf->Cell(40,10, $pdf->Image('test-image.png', 5, null));
$pdf->Ln(12);
$pdf->Cell( 0, 15, "Tests", 0, 0, 'L' );
$pdf->Ln(12);
$pdf->Cell(40,10, $pdf->Image('test-image1.png', 5, null));
$pdf->Ln(12);
$pdf->Cell( 0, 15, "Tests", 0, 0, 'L' );
$pdf->Output('I');

?>