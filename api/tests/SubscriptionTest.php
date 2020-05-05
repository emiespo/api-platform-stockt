<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Subscription;
use Faker\Provider\DateTime;

class SubscriptionTest extends TestCase
{
    public function testIsActive()
    {
        $subscription = new Subscription();

        $date = new \DateTime();
        $subscription->setActivationDate($date->sub(new \DateInterval('P2D')));
        $this->assertTrue($subscription->isActive());

        $subscription->setCancellationDate($date->sub(new \DateInterval('P1D')));
        $this->assertFalse($subscription->isActive());
    }
}
