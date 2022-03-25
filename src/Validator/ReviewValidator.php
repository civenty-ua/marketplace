<?php


namespace App\Validator;


use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReviewValidator extends ConstraintValidator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validate($review, Constraint $constraint)
    {
        if (!$constraint instanceof OnlySixReviews) {
            throw new UnexpectedTypeException($constraint, DateOfBirth::class);
        }
        $count = $this->em->getRepository(Review::class)->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.isTop = true')
            ->getQuery()
            ->getSingleScalarResult();
        if ($count >= 6 && $review->IsTop() == true) {
            $this->context->buildViolation($constraint->message)
                ->atPath('OnlySixReviews')
                ->addViolation();
        }

    }
}