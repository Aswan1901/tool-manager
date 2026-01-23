<?php

namespace App\Entity;

use App\Enums\AccessStatusType;
use App\Repository\UserToolAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserToolAccessRepository::class)]
class UserToolAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userToolAccesses')]
    private ?Users $userId = null;

    #[ORM\ManyToOne(inversedBy: 'userToolAccesses')]
    private ?Tools $toolId = null;

    #[ORM\Column]
    private ?int $grantedBy;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $revoked_at = null;

    #[ORM\Column(nullable: true)]
    private ?int $revokedBy = null;

    #[ORM\Column(enumType: AccessStatusType::class)]
    private ?AccessStatusType $status = AccessStatusType::ACTIVE;

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

    public function getGrantedBy(): ?int
    {
        return $this->grantedBy;
    }

    public function setGrantedBy(int $grantedBy): static
    {
        $this->grantedBy = $grantedBy;

        return $this;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revoked_at;
    }

    public function setRevokedAt(?\DateTimeImmutable $revoked_at): static
    {
        $this->revoked_at = $revoked_at;

        return $this;
    }

    public function getRevokedBy(): ?int
    {
        return $this->revokedBy;
    }

    public function setRevokedBy(?int $revokedBy): static
    {
        $this->revokedBy = $revokedBy;

        return $this;
    }

    public function getStatus(): ?AccessStatusType
    {
        return $this->status;
    }

    public function setStatus(AccessStatusType $status): static
    {
        $this->status = $status;

        return $this;
    }
}
