<?php

namespace App\Entity;

use App\Enums\DepartmentType;
use App\Enums\ToolStatusType;
use App\Repository\ToolsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolsRepository::class)]
class Tools
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $vendor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website_url = null;

    #[ORM\ManyToOne(inversedBy: 'tools')]
    #[ORM\JoinColumn(nullable: false)]
    private ?categories $categoryId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $monthly_cost = null;

    #[ORM\Column(nullable: false)]
    private ?int $active_user_count = 0;

    #[ORM\Column(enumType: DepartmentType::class)]
    private ?DepartmentType $owner_department = null;

    #[ORM\Column(enumType: ToolStatusType::class)]
    private ?ToolStatusType $status = ToolStatusType::active ;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at;

    /**
     * @var Collection<int, UserToolAccess>
     */
    #[ORM\OneToMany(targetEntity: UserToolAccess::class, mappedBy: 'toolId')]
    private Collection $userToolAccesses;

    /**
     * @var Collection<int, AccessRequests>
     */
    #[ORM\OneToMany(targetEntity: AccessRequests::class, mappedBy: 'toolId', orphanRemoval: true)]
    private Collection $accessRequests;

    /**
     * @var Collection<int, UsageLogs>
     */
    #[ORM\OneToMany(targetEntity: UsageLogs::class, mappedBy: 'toolId', orphanRemoval: true)]
    private Collection $usageLogs;

    #[ORM\ManyToOne(inversedBy: 'toolId')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CostTracking $costTracking = null;

    public function __construct()
    {
        $this->userToolAccesses = new ArrayCollection();
        $this->accessRequests = new ArrayCollection();
        $this->usageLogs = new ArrayCollection();
    }

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

    public function getCategoryId(): ?categories
    {
        return $this->categoryId;
    }

    public function setCategoryId(?categories $categoryId): static
    {
        $this->categoryId = $categoryId;

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

    public function getActiveUserCount(): ?int
    {
        return $this->active_user_count;
    }

    public function setActiveUserCount(int $active_user_count): static
    {
        $this->active_user_count = $active_user_count;

        return $this;
    }

    public function getOwnerDepartment(): ?DepartmentType
    {
        return $this->owner_department;
    }

    public function setOwnerDepartment(DepartmentType $owner_department): static
    {
        $this->owner_department = $owner_department;

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

    /**
     * @return Collection<int, UserToolAccess>
     */
    public function getUserToolAccesses(): Collection
    {
        return $this->userToolAccesses;
    }

    public function addUserToolAccess(UserToolAccess $userToolAccess): static
    {
        if (!$this->userToolAccesses->contains($userToolAccess)) {
            $this->userToolAccesses->add($userToolAccess);
            $userToolAccess->setToolId($this);
        }

        return $this;
    }

    public function removeUserToolAccess(UserToolAccess $userToolAccess): static
    {
        if ($this->userToolAccesses->removeElement($userToolAccess)) {
            // set the owning side to null (unless already changed)
            if ($userToolAccess->getToolId() === $this) {
                $userToolAccess->setToolId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccessRequests>
     */
    public function getAccessRequests(): Collection
    {
        return $this->accessRequests;
    }

    public function addAccessRequest(AccessRequests $accessRequest): static
    {
        if (!$this->accessRequests->contains($accessRequest)) {
            $this->accessRequests->add($accessRequest);
            $accessRequest->setToolId($this);
        }

        return $this;
    }

    public function removeAccessRequest(AccessRequests $accessRequest): static
    {
        if ($this->accessRequests->removeElement($accessRequest)) {
            // set the owning side to null (unless already changed)
            if ($accessRequest->getToolId() === $this) {
                $accessRequest->setToolId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UsageLogs>
     */
    public function getUsageLogs(): Collection
    {
        return $this->usageLogs;
    }

    public function addUsageLog(UsageLogs $usageLog): static
    {
        if (!$this->usageLogs->contains($usageLog)) {
            $this->usageLogs->add($usageLog);
            $usageLog->setToolId($this);
        }

        return $this;
    }

    public function removeUsageLog(UsageLogs $usageLog): static
    {
        if ($this->usageLogs->removeElement($usageLog)) {
            // set the owning side to null (unless already changed)
            if ($usageLog->getToolId() === $this) {
                $usageLog->setToolId(null);
            }
        }

        return $this;
    }

    public function getCostTracking(): ?CostTracking
    {
        return $this->costTracking;
    }

    public function setCostTracking(?CostTracking $costTracking): static
    {
        $this->costTracking = $costTracking;

        return $this;
    }
}
