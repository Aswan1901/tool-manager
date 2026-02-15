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

    #[ORM\Column(length: 150, unique: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'department', enumType: DepartmentType::class)]
    private ?DepartmentType $departmentType = null;


    #[ORM\Column(enumType: UserRoleType::class)]
    private UserRoleType $role = UserRoleType::employee;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $hire_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(name:'status' ,enumType: UserStatusType::class)]
    private ?UserStatusType $userStatusActive = UserStatusType::active;

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

    public function getRole(): ?UserRoleType
    {
        return $this->role;
    }

    public function setRole(UserRoleType $role): static
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
        return $this->userStatusActive;
    }

    public function setUserStatusActive(UserStatusType $userStatusActive): static
    {
        $this->userStatusActive = $userStatusActive;

        return $this;
    }
}
