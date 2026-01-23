<?php

namespace App\Entity;

use App\Enums\DepartmentType;
use App\Enums\ToolStatusType;
use App\Repository\ToolsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ToolsRepository::class)]
class Tools
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?string $vendor = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?string $website_url = null;

    #[ORM\ManyToOne(inversedBy: 'tools')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?Categories $category = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?string $monthly_cost = null;

    #[ORM\Column(nullable: false)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?int $active_users_count = 0;

    #[ORM\Column(enumType: DepartmentType::class)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?DepartmentType $ownerDepartment = null;

    #[ORM\Column(enumType: ToolStatusType::class)]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?ToolStatusType $status = ToolStatusType::active ;

    #[ORM\Column]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column]
    #[Groups(['tool:list', 'tool:detail'])]
    private ?\DateTimeImmutable $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function setVendor(?string $vendor): static
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->website_url;
    }

    public function setWebsiteUrl(?string $website_url): static
    {
        $this->website_url = $website_url;

        return $this;
    }

    public function getCategory(): ?categories
    {
        return $this->category;
    }

    public function setCategory(?categories $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMonthlyCost(): ?string
    {
        return $this->monthly_cost;
    }

    public function setMonthlyCost(string $monthly_cost): static
    {
        $this->monthly_cost = $monthly_cost;

        return $this;
    }

    public function getActiveUsersCount(): ?int
    {
        return $this->active_users_count;
    }

    public function setActiveUserCount(int $active_users_count): static
    {
        $this->active_users_count = $active_users_count;

        return $this;
    }

    public function getOwnerDepartment(): ?DepartmentType
    {
        return $this->ownerDepartment;
    }

    public function setOwnerDepartment(DepartmentType $ownerDepartment): static
    {
        $this->ownerDepartment = $ownerDepartment;

        return $this;
    }

    public function getStatus(): ?ToolStatusType
    {
        return $this->status;
    }

    public function setStatus(ToolStatusType $status): static
    {
        $this->status = $status;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
