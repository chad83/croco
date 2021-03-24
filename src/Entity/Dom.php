<?php

namespace App\Entity;

use App\Repository\DomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DomRepository::class)
 * @ORM\Table(indexes={@ORM\Index(columns={"type"})})
 * @ORM\Table(indexes={@ORM\Index(columns={"file_type"})})
 */
class Dom
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $file_type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $file_size;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $parent_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file_path;

    /**
     * @ORM\ManyToOne(targetEntity=Job::class, inversedBy="pages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $job;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->file_type;
    }

    public function setFileType(?string $file_type): self
    {
        $this->file_type = $file_type;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(?int $file_size): self
    {
        $this->file_size = $file_size;

        return $this;
    }

    public function getParentUrl(): ?string
    {
        return $this->parent_url;
    }

    public function setParentUrl(?string $parent_url): self
    {
        $this->parent_url = $parent_url;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(?string $file_name): self
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(?string $file_path): self
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;

        return $this;
    }
}
