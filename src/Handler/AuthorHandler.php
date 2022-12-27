<?php
// api/src/Handler/PersonHandler.php

namespace App\Handler;

use App\Entity\Author;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AuthorHandler implements MessageHandlerInterface
{
    public function __invoke(Author $author)
    {
        return $author->getName();
    }
}