<?php

namespace Dedoc\Scramble\Support\ExceptionToResponseExtensions;

use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Validation\ValidationException;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;

class ValidationExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type)
    {
        return $type instanceof ObjectType
            && $type->isInstanceOf(ValidationException::class);
    }

    public function toResponse(Type $type)
    {
        $validationResponseBodyType = (new OpenApiTypes\ObjectType())
            ->addProperty(
                'message',
                (new OpenApiTypes\StringType())
                    ->setDescription('Errors overview.')
            )
            ->addProperty(
                'errors',
                (new OpenApiTypes\ObjectType())
                    ->setDescription('A detailed description of each field that failed validation.')
                    ->additionalProperties((new OpenApiTypes\ArrayType)->setItems(new OpenApiTypes\StringType()))
            )
            ->setRequired(['message', 'errors']);

        return Response::make(422)
            ->description('Validation error')
            ->setContent(
                'application/json',
                Schema::fromType($validationResponseBodyType)
            );
    }

    public function reference(ObjectType $type)
    {
        return new Reference('responses', $type->name, $this->components);
    }
}
