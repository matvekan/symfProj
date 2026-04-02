<?php

namespace App\Validator\Constraint;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EntityExistsValidator extends ConstraintValidator
{

    public function __construct(private EntityManagerInterface $em)
    {
    }


    public function validate(mixed $value, Constraint $constraint)
    {
        if (!is_iterable($value)) {
            return;
        }

        foreach ($value as $interestId) {
            $interest=$this->em->getRepository($constraint->entity)->find($interestId);
            if(!$interest){
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{entity}}', $constraint->entity)
                    ->setParameter('{{id}}', $interestId)
                    ->addViolation();
            }
        }


    }
}
