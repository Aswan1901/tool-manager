<?php

namespace App\Entity;

use App\Enums\RequestStatusType;
use App\Repository\AccessRequestsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRequestsRepository::class)]
class AccessRequests
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accessRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToOne(inversedBy: 'accessRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tools $tool = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $BusinessJustification = null;

    #[ORM\Column(enumType: RequestStatusType::class)]
    private ?RequestStatusType $status = RequestStatusType::pending;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $requested_at;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $processed_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $processingNotes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

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

    public function getBusinessJustification(): ?string
    {
        return $this->BusinessJustification;
    }

    public function setBusinessJustification(string $BusinessJustification): static
    {
        $this->BusinessJustification = $BusinessJustification;

        return $this;
    }

    public function getStatus(): ?RequestStatusType
    {
        return $this->status;
    }

    public function setStatus(RequestStatusType $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requested_at;
    }

    public function setRequestedAt(?\DateTimeImmutable $requested_at): static
    {
        $this->requested_at = $requested_at;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processed_at;
    }

    public function setProcessedAt(?\DateTimeImmutable $processed_at): static
    {
        $this->processed_at = $processed_at;

        return $this;
    }

    public function getProcessingNotes(): ?string
    {
        return $this->processingNotes;
    }

    public function setProcessingNotes(?string $processingNotes): static
    {
        $this->processingNotes = $processingNotes;

        return $this;
    }
}
