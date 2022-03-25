<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ExportService
 * @package App\Service
 */
class ExportService
{
    private SessionInterface $session;

    private RequestStack $request;

    public function __construct(SessionInterface $session, RequestStack $request)
    {
        $this->session = $session;
        $this->request = $request;
    }

    public function addMessage($message, $type = 'notice')
    {
        $this->session->getFlashBag()->add(
            $type,
            $message);
    }

    public function getMessages()
    {
        return $this->session->getFlashBag()->all();
    }

    /**
     * @param string $entityName
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function export(string $entityName, array $data)
    {
        $response = new RedirectResponse($this->request->getMasterRequest()->query->get('referrer'));

        if (!$entityName) {
            $this->addMessage('EntityName is required', 'danger');

            return $response->send();
        }

        if (!$data) {
            $this->addMessage('Data is required', 'danger');

            return $response->send();
        }

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        $headers = array_keys($data[0]);

        // write headers
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow(++$i, $row, $header);
        }

        // write values
        foreach ($data as $entry) {
            $row++;

            $values = array_values($entry);

            foreach ($values as $i => $value) {
                $sheet->setCellValueByColumnAndRow(++$i, $row, $value);
            }
        }

        $fileName = 'export' . '_' . $entityName . '_' . (new \DateTime)->format('Y-m-d');

        if (strlen($fileName) >= 31) {
                $fileName = $entityName . '_' . (new \DateTime)->format('Y-m-d');
        }
        $sheet->setTitle($fileName);

        // Create and force download the file
        $streamedResponse = new StreamedResponse();

        $streamedResponse->setCallback(function () use ($spreadsheet) {
            $writer = new Csv($spreadsheet);
            $writer->save('php://output');
        });

        $streamedResponse->setStatusCode(200);
        $streamedResponse->headers->set('Content-Type', 'application/csv');
        $streamedResponse->headers->set('Content-Disposition', 'attachment;filename=' . $fileName . '.csv');

        return $streamedResponse->send();
    }
}