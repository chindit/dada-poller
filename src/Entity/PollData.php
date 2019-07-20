<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table("statistics",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="poll_data_unique",
 *            columns={"key_data", "date_data"})
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
	 * @ORM\Column(type="string", length=25, name="key_data")
	 */
	private $key;

	/**
	 * @ORM\Column(type="string", length=75)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	private $category;

	/**
	 * @ORM\Column(type="float", name="value_data")
	 */
	private $value;

	/**
	 * @ORM\Column(type="datetime", name="date_data")
	 */
	private $date;

	public function __construct(?string $key = null, ?float $value = null, ?string $name = null)
	{
		if (null !== $key && null !== $value)
		{
			$this->key = $key;
			$this->value = $value;
		}

		$this->name = $name;
		$this->date = new DateTimeImmutable();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getKey(): ?string
	{
		return $this->key;
	}

	public function setKey(string $key): self
	{
		$this->key = $key;

		return $this;
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
		return $this->date ?? new DateTimeImmutable();
	}

	public function setDate(DateTimeInterface $date): self
	{
		$this->date = $date;

		return $this;
	}
}
