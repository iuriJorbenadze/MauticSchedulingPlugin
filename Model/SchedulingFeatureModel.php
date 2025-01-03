<?php

namespace MauticPlugin\SchedulingFeatureBundle\Model;

class SchedulingFeatureModel
{
    private ?string $firstName;
    private ?string $lastName;
    private ?string $email;

    public function __construct(array $data)
    {
        // Validate and assign properties
        $this->firstName = $data['First Name'] ?? null;
        $this->lastName = $data['Last Name'] ?? null;

        // Allow any valid or empty email
        $this->email = $data['Email'] ?? null;

        // if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
        //     throw new \InvalidArgumentException('Invalid email address provided.');
        // }
    }

    // Getters
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Serialize data for database insertion
    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }
}
