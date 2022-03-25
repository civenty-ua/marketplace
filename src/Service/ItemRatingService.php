<?php


namespace App\Service;


use App\Entity\FeedbackFormQuestion;
use App\Entity\UserItemFeedback;
use App\Entity\UserItemFeedbackAnswer;
use Doctrine\ORM\EntityManagerInterface;


class ItemRatingService
{
    private const STANDARD_USER_COUNT = 100;
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function updateRating(object $object)
    {
        $this->isItemChild($object);

        $rating = $this->getRatingFromFeedback($object);

        if (!is_null($rating) && $rating != 0) {
            $userFeedback = $this->getObjectFeedBackForm($object);
            $voted = count($userFeedback) + $object->getOldUserCount();
            if ($object->getRating() != 0 && $object->getOldUserCount()) {
                $rate = ($object->getRating() * $object->getOldUserCount() + $rating * count($userFeedback)) / $voted;
            } elseif ($object->getRating() != 0 && !$object->getOldUserCount()) {
                $rate = ($object->getRating() * self::STANDARD_USER_COUNT + $rating * count($userFeedback)) / ($voted + self::STANDARD_USER_COUNT);
            } else {
                $rate = $rating;
            }

            if ($object->getNewRating() != $rate) {
                $object->setNewRating($rate);
            }


            $voted = count($userFeedback) + $object->getOldUserCount();
        } else {
            $rate = $object->getRating();
            $voted = $object->getOldUserCount();
        }

        $rate = round($rate, 1);

        return [
            'rate' => $rate,
            'voted' => $voted
        ];
    }

    public function getReviews(object $object)
    {
        $review = [];
        if ($object->getFeedbackForm()) {
            $userFeedback = $this->em->getRepository(UserItemFeedback::class)
                ->getTopUserFeedBack($object->getId(), $object->getFeedBackForm()->getId());
            $questions = $this->em->getRepository(FeedbackFormQuestion::class)
                ->findQuestionsToDisplay($object->getFeedbackForm()->getId());
            if (!empty($questions)) {
                $userAnswers = $this->em->getRepository(UserItemFeedbackAnswer::class)
                    ->findAnswers($questions, $userFeedback);
            }
            if (!empty($userAnswers)) {
                foreach ($userAnswers as $userAnswer) {
                    $review[$userAnswer->getId()]['answer'] = $userAnswer->getAnswer();
                    $review[$userAnswer->getId()]['username'] = $userAnswer->getUserFeedBack()->getUser()->getName();
                }
            }
        }
        return $review;
    }

    public function updateWebinarRatingByAjax(object $object, float $jsonRating)
    {
        if ($jsonRating == 0 || !$jsonRating) {
            return;
        }

        if ($object->getRating() && $object->getOldUserCount()) {
            $newRatingFromJson = ($object->getRating() * $object->getOldUserCount() + $jsonRating) / ($object->getOldUserCount() + 1);
            $newRatingFromJson = round($newRatingFromJson, 3);

            $object->setRating($newRatingFromJson);
            $object->setOldUserCount($object->getOldUserCount() + 1);
        }
        elseif ($object->getRating() != 0 && !$object->getOldUserCount()) {
            $newRatingFromJson = ($object->getRating() + $jsonRating) / (2);
            $newRatingFromJson = round($newRatingFromJson, 3);
            $object->setRating($newRatingFromJson);
            $object->setOldUserCount(2);
        }
        elseif ((int)$object->getRating() == 0 && (int)$object->getOldUserCount() == 0) {
            $object->setOldUserCount(1);
            $object->setRating($jsonRating);
        }
        $this->em->flush();
    }

    private function isItemChild(object $object)
    {
        if (!is_subclass_of($object, 'App\Entity\Item')) {
            throw new \Exception('Not child of Item');
        }
    }

    private function getRatingFromFeedback(object $object)
    {
        $rating = 0;
        if ($object->getFeedbackForm()) {
            $userFeedback = $this->getObjectFeedBackForm($object);
            $questions = $this->getRatingQuestions($object);

            if (!empty($questions)) {
                $rating = $this->em->getRepository(UserItemFeedbackAnswer::class)
                    ->countRating($questions, $userFeedback);
            }
        }
        return $rating;
    }

    private function getObjectFeedBackForm(object $object)
    {
        if ($object->getFeedbackForm()) {
            return $this->em->getRepository(UserItemFeedback::class)
                ->getUserFeedBackForEstimate($object->getId(), $object->getFeedBackForm()->getId());
        }
    }

    private function getRatingQuestions(object $object)
    {
        return $this->em->getRepository(FeedbackFormQuestion::class)
            ->findRatingQuestions($object->getFeedbackForm()->getId());
    }
}