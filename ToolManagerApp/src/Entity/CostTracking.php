<?php

namespace App\Entity;

use App\Repository\CostTrackingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CostTrackingRepository::class)]
class CostTracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Tools>
     */
    #[ORM\OneToMany(targetEntity: Tools::class, mappedBy: 'costTracking')]
    private Collection $toolId;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $month_year = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalMonthlyCost = null;

    #[ORM\Column]
    private ?int $activeUserCount = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->toolId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Tools>
     */
    public function getToolId(): Collection
    {
        return $this->toolId;
    }

    public function addToolId(Tools $toolId): static
    {
        if (!$this->toolId->contains($toolId)) {
            $this->toolId->add($toolId);
            $toolId->setCostTracking($this);
        }

        return $this;
    }

    public function removeToolId(Tools $toolId): static
    {
        if ($this->toolId->removeElement($toolId)) {
            // set the owning side to null (unless already changed)
            if ($toolId->getCostTracking() === $this) {
                $toolId->setCostTracking(null);
            }
        }

        return $this;
    }

    public function getMonthYear(): ?\DateTime
    {
        return $this->month_year;
    }

    public function setMonthYear(\DateTime $month_year): static
    {
        $this->month_year = $month_year;

        return $this;
    }

    public function getTotalMonthlyCost(): ?string
    {
        return $this->totalMonthlyCost;
    }

    public function setTotalMonthlyCost(string $totalMonthlyCost): static
    {
        $this->totalMonthlyCost = $totalMonthlyCost;

        return $this;
    }

    public function getActiveUserCount(): ?int
    {
        return $this->activeUserCount;
    }

    public function setActiveUserCount(int $activeUserCount): static
    {
        $this->activeUserCount = $activeUserCount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
