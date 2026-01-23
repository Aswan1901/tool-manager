<?php

namespace App\Entity;

use App\Repository\UsageLogsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsageLogsRepository::class)]
class UsageLogs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'usageLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $userId = null;

    #[ORM\ManyToOne(inversedBy: 'usageLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tools $toolId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $sessionDate = null;

    #[ORM\Column]
    private ?int $usageMinutes = null;

    #[ORM\Column]
    private ?int $actionCount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?Users
    {
        return $this->userId;
    }

    public function setUserId(?Users $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getToolId(): ?Tools
    {
        return $this->toolId;
    }

    public function setToolId(?Tools $toolId): static
    {
        $this->toolId = $toolId;

        return $this;
    }

    public function getSessionDate(): ?\DateTime
    {
        return $this->sessionDate;
    }

    public function setSessionDate(\DateTime $sessionDate): static
    {
        $this->sessionDate = $sessionDate;

        return $this;
    }

    public function getUsageMinutes(): ?int
    {
        return $this->usageMinutes;
    }

    public function setUsageMinutes(int $usageMinutes): static
    {
        $this->usageMinutes = $usageMinutes;

        return $this;
    }

    public function getActionCount(): ?int
    {
        return $this->actionCount;
    }

    public function setActionCount(int $actionCount): static
    {
        $this->actionCount = $actionCount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
