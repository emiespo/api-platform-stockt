<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PlanRepository")
 */
class Plan
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3)
     * @Assert\NotBlank
     */
    private $currency;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\Range(min=0, minMessage="The monthly cost must be superior to 0.")
     * @Assert\NotBlank
     */
    private $monthlyCost;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\Range(min=0, minMessage="The annual cost must be superior to 0.")
     * @Assert\NotBlank
     */
    private $annualCost;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="savedPlans")
     */
    private $savedPlans;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="plan")
     */
    private $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getMonthlyCost(): ?string
    {
        return $this->monthlyCost;
    }

    public function setMonthlyCost(string $monthlyCost): self
    {
        $this->monthlyCost = $monthlyCost;

        return $this;
    }

    public function getAnnualCost(): ?string
    {
        return $this->annualCost;
    }

    public function setAnnualCost(string $annualCost): self
    {
        $this->annualCost = $annualCost;

        return $this;
    }

    public function getSavedPlans(): ?User
    {
        return $this->savedPlans;
    }

    public function setSavedPlans(?User $savedPlans): self
    {
        $this->savedPlans = $savedPlans;

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setPlan($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getPlan() === $this) {
                $subscription->setPlan(null);
            }
        }

        return $this;
    }
}
