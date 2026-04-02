<?php

namespace App\DTOValidator;

use App\DTO\Input\User\StoreUserInputDTO;
use App\Exception\ValidateException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDTOValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ){

    }

    public function validate(StoreUserInputDTO $user):void{
        $errors=$this->validator->validate($user);
        if(count($errors)>0){
            $messages=[];
            foreach($errors as $error){
                $messages[$error->getPropertyPath()][]=$error->getMessage();
            }
            throw new ValidateException($messages);

        }

    }

}
