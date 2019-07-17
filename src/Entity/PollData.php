<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table("statistics",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="poll_data_unique",
 *            columns={"name", "date"})
 *     }
 * )
 */
class PollData
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $name;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $category;

	/**
	 * @ORM\Column(type="float")
	 */
	private $value;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $date;

	public function __construct(?string $name = null, ?float $value = null)
	{
		if (null !== $name && null !== $value)
		{
			$this->name = $name;
			$this->value = $value;
		}

		$this->date = new \DateTimeImmutable();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

	public function getValue(): ?float
	{
		return $this->value;
	}

	public function setValue(float $value): self
	{
		$this->value = $value;

		return $this;
	}

	public function getDate(): ?DateTimeInterface
	{
		return $this->date ?? new \DateTimeImmutable();
	}

	public function setDate(DateTimeInterface $date): self
	{
		$this->date = $date;

		return $this;
	}
}
