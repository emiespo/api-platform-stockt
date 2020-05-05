<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

use App\Controller\SaveUserSubscriptions;
use App\Controller\GetUserSubscriptionsCost;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={
 *          "get",
 *          "put",
 *          "delete",
 *          "save_subscriptions"={
 *             "method"="POST",
 *             "path"="/users/{id}/save_subscriptions",
 *             "controller"="App\Controller\SaveUserSubscriptions::class"
 *          },
 *          "get_subscriptions_cost"={
 *              "method"="GET",
 *              "path"="/users/{id}/get_subscriptions_cost",
 *              "controller"=GetUserSubscriptionsCost::class,
 *              "messenger"=true
 *          },
 *          "subscriptions"={
 *              "method"="GET",
 *              "path"="/users/{id}/subscriptions",
 *          }
 *     },
 *     collectionOperations={
 *          "get",
 *          "post",
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Plan", mappedBy="savedPlans", cascade={"persist", "remove"})
     */
    private $savedPlans;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="user", orphanRemoval=true)
     */
    private $subscriptions;

    public function __construct()
    {
        $this->savedPlans = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|Plan[]
     */
    public function getSavedPlans(): Collection
    {
        return $this->savedPlans;
    }

    public function addSavedPlan(Plan $savedPlan): self
    {
        if (!$this->savedPlans->contains($savedPlan)) {
            $this->savedPlans[] = $savedPlan;
            $savedPlan->setSavedPlans($this);
        }

        return $this;
    }

    public function removeSavedPlan(Plan $savedPlan): self
    {
        if ($this->savedPlans->contains($savedPlan)) {
            $this->savedPlans->removeElement($savedPlan);
            // set the owning side to null (unless already changed)
            if ($savedPlan->getSavedPlans() === $this) {
                $savedPlan->setSavedPlans(null);
            }
        }

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
            $subscription->setUser($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getUser() === $this) {
                $subscription->setUser(null);
            }
        }

        return $this;
    }

}
