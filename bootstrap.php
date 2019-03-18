<?php
require 'vendor/autoload.php';
include_once 'PHPWord.php';

$templateFilename = "bitlocker_password_template.dotx";
$buildWordFilename = "./hello.docx";
$buildPdfFilename = "./hello.pdf";

$template = new \PhpOffice\PhpWord\TemplateProcessor($templateFilename);

$template->setValue('date', date('Y年m月d日'));
$template->setValue('serial_number', 'NP-1234');
$template->setValue('password', 'abcdefgh!!');

$template->saveAs($buildWordFilename);

Gears\Pdf::convert($buildWordFilename, $buildPdfFilename);
