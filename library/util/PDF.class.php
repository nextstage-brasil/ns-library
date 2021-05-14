<?php

require __DIR__ . '/fpdf/fpdf.class.php';

class PDF extends FPDF {

    var $title, $logo, $status;

    function __construct($title = '', $logo = 'logo.png', $status = '', $orientation = 'P', $unit = 'mm', $size = 'A4') {
        // Call parent constructor
        $this->FPDF($orientation, $unit, $size);
        // Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->title = $title;
        $this->logo = $logo;
        $this->status = $status;
    }

// Page header
    function Header() {
        // Logo
        $this->Image($this->logo, 10, 6, 15);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 20);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, $this->title, 0, 0, 'C');
        $this->ln();
        $this->SetFont('Arial', '', 12);
        // Line break
        $this->cell(190, 5, $this->status, 0, 0, 'C');
        $this->Ln(10);
    }

// Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-10);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(60, 10, $this->title, 0, 0, 'L');
        $this->Cell(70, 10, utf8_decode('Página ' . $this->PageNo()), 0, 0, 'C');
        $this->Cell(60, 10, 'Emitido em: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
    }

}

?>