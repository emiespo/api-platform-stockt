<?php
// api/src/Controller/CreateBookPublication.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Handler\UserSubscriptionSaveHandler;
use Symfony\Component\Routing\Annotation\Route;

class SaveUserSubscriptions
{
    private $userSubscriptionHandler;

    public function __construct(UserSubscriptionSaveHandler $userSubscriptionHandler)
    {
        $this->userSubscriptionHandler = $userSubscriptionHandler;
    }

    /**
     * @param Subscription $data
     * @return Subscription
     */
    public function __invoke(Subscription $data): Subscription
    {
        $this->userSubscriptionHandler->handle($data);

        return $data;
    }
}
