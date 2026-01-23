<?php

namespace App\Entity;

use App\Enums\DepartmentType;
use App\Enums\UserStatusType;
use App\Enums\UserRoleType;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 150)]
    private ?string $email = null;

    #[ORM\Column(enumType: DepartmentType::class)]
    private ?DepartmentType $departmentType = null;

    #[ORM\Column(enumType: UserRoleType::class)]
    private UserRoleType $role = UserRoleType::employee;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $hire_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(enumType: UserStatusType::class)]
    private ?UserStatusType $user_status_active = UserStatusType::active;

    /**
     * @var Collection<int, UserToolAccess>
     */
    #[ORM\OneToMany(targetEntity: UserToolAccess::class, mappedBy: 'userId')]
    private Collection $userToolAccesses;

    /**
     * @var Collection<int, AccessRequests>
     */
    #[ORM\OneToMany(targetEntity: AccessRequests::class, mappedBy: 'userId', orphanRemoval: true)]
    private Collection $accessRequests;

    /**
     * @var Collection<int, UsageLogs>
     */
    #[ORM\OneToMany(targetEntity: UsageLogs::class, mappedBy: 'userId', orphanRemoval: true)]
    private Collection $usageLogs;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDepartmentType(): ?DepartmentType
    {
        return $this->departmentType;
    }

    public function setDepartmentType(DepartmentType $departmentType): static
    {
        $this->departmentType = $departmentType;

        return $this;
    }

    public function getRole(): ?UserStatusType
    {
        return $this->role;
    }

    public function setRole(UserStatusType $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getHireDate(): ?\DateTime
    {
        return $this->hire_date;
    }

    public function setHireDate(?\DateTime $hire_date): static
    {
        $this->hire_date = $hire_date;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUserStatusActive(): ?UserStatusType
    {
        return $this->user_status_active;
    }

    public function setUserStatusActive(UserStatusType $user_status_active): static
    {
        $this->user_status_active = $user_status_active;

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
            $userToolAccess->setUserId($this);
        }

        return $this;
    }

    public function removeUserToolAccess(UserToolAccess $userToolAccess): static
    {
        if ($this->userToolAccesses->removeElement($userToolAccess)) {
            // set the owning side to null (unless already changed)
            if ($userToolAccess->getUserId() === $this) {
                $userToolAccess->setUserId(null);
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
            $accessRequest->setUserId($this);
        }

        return $this;
    }

    public function removeAccessRequest(AccessRequests $accessRequest): static
    {
        if ($this->accessRequests->removeElement($accessRequest)) {
            // set the owning side to null (unless already changed)
            if ($accessRequest->getUserId() === $this) {
                $accessRequest->setUserId(null);
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
            $usageLog->setUserId($this);
        }

        return $this;
    }

    public function removeUsageLog(UsageLogs $usageLog): static
    {
        if ($this->usageLogs->removeElement($usageLog)) {
            // set the owning side to null (unless already changed)
            if ($usageLog->getUserId() === $this) {
                $usageLog->setUserId(null);
            }
        }

        return $this;
    }
}
