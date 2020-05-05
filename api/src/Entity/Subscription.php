<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={
 *          "get",
 *          "put",
 *          "delete",
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 */
class Subscription
{
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_ANNUAL = 'annual';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Plan", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(groups={"userSaveSubscription"})
     */
    private $plan;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subscriptions")
     * @Assert\NotBlank(groups={"userSaveSubscription"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"userSaveSubscription"})
     */
    private $billingFrequency;

    /**
     * @ORM\Column(type="datetimetz")
         * @Assert\NotBlank(groups={"userSaveSubscription"})
     */
    private $activationDate;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $cancellationDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBillingFrequency(): ?string
    {
        return $this->billingFrequency;
    }

    public function setBillingFrequency(string $billingFrequency): self
    {
        if (!in_array($billingFrequency, array(self::FREQUENCY_ANNUAL, self::FREQUENCY_MONTHLY))) {
            throw new \InvalidArgumentException("Invalid frequency.");
        }
        $this->billingFrequency = $billingFrequency;

        return $this;
    }

    public function getActivationDate(): ?\DateTimeInterface
    {
        return $this->activationDate;
    }

    public function setActivationDate(\DateTimeInterface $activationDate): self
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): self
    {
        $this->cancellationDate = $cancellationDate;

        return $this;
    }

    public function isActive(): bool
    {
        return empty($this->cancellationDate) || $this->cancellationDate > new \DateTime();
    }
}
