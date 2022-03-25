<?php

namespace App\Helper;

use App\Service\ZoomClient;

class ZoomRequestHelper
{
    public function parseMeetingQuestions(ZoomClient $zoomClient, int $meetingId)
    {
        $meetingExists = $zoomClient->doRequest('GET', "/meetings/$meetingId");
        if (!array_key_exists('join_url', $meetingExists)) {
            return [];
        }
        $response = $zoomClient->doRequest('GET', "/meetings/$meetingId/registrants/questions");
        $requiredQuestions = [];
        if (array_key_exists('custom_questions', $response)) {
            foreach ($response['custom_questions'] as $question) {
                if ($question['required'] === true) {
                    $requiredQuestions[] = $question;
                }
            }
        }
        return $requiredQuestions;
    }

    public function getAnswersForRequiredQuestions(array $requiredQuestions)
    {
        if (empty($requiredQuestions)) {
            return;
        }
        foreach ($requiredQuestions as $key => $question) {
            if ($question['type'] == 'short') {
                $answers[$key]['title'] = $question['title'];
                $answers[$key]['value'] = 'User From UHDP';
            } elseif ($question['type'] == 'single') {
                $answers[$key]['title'] = $question['title'];
                $answers[$key]['value'] = $question['answers'][rand(0, count($question['answers']) - 1)];
            }
        }

        return $answers;
    }

    public function getZoomWebinarStartDate(array $zoomWebinarResponse): ?\DateTime
    {
        if ($zoomWebinarResponse) {
            if ($this->checkStartTimeIsDefinedInWebinarResponse($zoomWebinarResponse)) {
                $startDate = new \DateTime($zoomWebinarResponse['start_time']);
            } elseif ($this->checkOccurrencesIsDefinedInWebinarResponse($zoomWebinarResponse)) {
                $startDate = $this->getStartDateFromOccurrence($zoomWebinarResponse);
            }
        }

        return $startDate;
    }

    private function checkStartTimeIsDefinedInWebinarResponse(array $zoomWebinarResponse): bool
    {
        return array_key_exists('start_time', $zoomWebinarResponse);
    }

    private function checkOccurrencesIsDefinedInWebinarResponse(array $zoomWebinarResponse): bool
    {
        return array_key_exists('occurrences', $zoomWebinarResponse) && !empty($zoomWebinarResponse['occurrences']);
    }


    private function getStartDateFromOccurrence(array $zoomWebinarResponse): ?\DateTime
    {
        foreach ($zoomWebinarResponse['occurrences'] as $occurrence) {
            if (array_key_exists('status', $occurrence) && $occurrence['status'] == 'available') {
                if (new \DateTime($occurrence['start_time']) > new \DateTime('now')){
                    return new \DateTime($occurrence['start_time']);
                }
            }
        }
        return null;
    }
}