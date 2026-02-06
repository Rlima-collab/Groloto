<?php

namespace App\Entity;

use App\Repository\ExcelDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: ExcelDataRepository::class)]
#[ORM\Table(name: 'EXCEL_DATA')]
class ExcelData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $sheet_name = null;

    #[ORM\Column(type: 'json')]
    private array $columns = [];

    #[ORM\Column(type: 'json')]
    private array $data = [];

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $imported_at = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $original_filename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSheetName(): ?string
    {
        return $this->sheet_name;
    }

    public function setSheetName(string $sheet_name): self
    {
        $this->sheet_name = $sheet_name;
        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getImportedAt(): ?DateTime
    {
        return $this->imported_at;
    }

    public function setImportedAt(DateTime $imported_at): self
    {
        $this->imported_at = $imported_at;
        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->original_filename;
    }

    public function setOriginalFilename(?string $original_filename): self
    {
        $this->original_filename = $original_filename;
        return $this;
    }

    public function getRowCount(): int
    {
        return count($this->data);
    }
}
