<?php
require_once __DIR__ . '/vendor/autoload.php';

class TemplateWorker {
  public function exec($data) {
    $templateFilename = $data['template_filename'];
    $jobid = str_replace('-', '', $data['jobid']);
    $buildWordFilename = "./work/".$jobid.".docx";
    $buildPdfFilename = $data['exportpdfname'];

    $template = new \PhpOffice\PhpWord\TemplateProcessor($templateFilename);

    $template->setValue('date', $data['display_date']);
    $template->setValue('serial_number', $data['serial_number']);
    $template->setValue('password', $data['password']);

    $template->saveAs($buildWordFilename);

    Gears\Pdf::convert($buildWordFilename, $buildPdfFilename);

    return array("msg" => "ok", "jobid" => $jobid, "filename" => $buildPdfFilename);
  }
}
