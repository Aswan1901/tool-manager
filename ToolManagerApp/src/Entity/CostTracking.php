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

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $month_year = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalMonthlyCost = null;

    #[ORM\Column]
    private ?int $activeUsersCount = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'costTrackings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tools $tool = null;

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

    public function getActiveUsersCount(): ?int
    {
        return $this->activeUsersCount;
    }

    public function setActiveUsersCount(int $activeUsersCount): static
    {
        $this->activeUsersCount = $activeUsersCount;

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

    public function getTool(): ?Tools
    {
        return $this->tool;
    }

    public function setTool(?Tools $tool): static
    {
        $this->tool = $tool;

        return $this;
    }
}
