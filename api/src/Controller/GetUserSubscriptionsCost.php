<?php
// api/src/Controller/CreateBookPublication.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetUserSubscriptionsCost
{
    /**
     * @Route(
     *     name="get_subscriptions_cost",
     *     path="/users/{id}/get_subscriptions_cost",
     *     methods={"POST"},
     *     defaults={
     *         "_api_resource_class"=User::class,
     *         "_api_item_operation_name"="get_subscriptions_cost"
     *     }
     * )
     * @param User $data
     * @return User
     * @throws \Exception
     */
    public function __invoke(User $data): string
    {
        $subscriptions = $data->getSubscriptions();

        $subscriptionMonthlyCost = 0;
        $subscriptionAnnualCost = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->isActive()) {
                $plan = $subscription->getPlan();
                if ($subscription->getBillingFrequency() === Subscription::FREQUENCY_ANNUAL) {
                    $subscriptionAnnualCost += $plan->getAnnualCost();
                } else {
                    $subscriptionMonthlyCost += $plan->getMonthlyCost();
                }
            }
        }

        return new JsonResponse(['monthlyCost' => $subscriptionMonthlyCost, 'annualCost' => $subscriptionAnnualCost]);
    }
}
