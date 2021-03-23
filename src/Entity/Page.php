<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Page::class)
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity=Dom::class, inversedBy="pages")
     */
    private $dom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status_code;

    /**
     * @ORM\ManyToOne(targetEntity=Job::class, inversedBy="pages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $job;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function __construct()
    {
        $this->link = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|self[]
     */
    public function getLink(): Collection
    {
        return $this->link;
    }

    public function addLink(self $link): self
    {
        if (!$this->link->contains($link)) {
            $this->link[] = $link;
        }

        return $this;
    }

    public function removeLink(self $link): self
    {
        $this->link->removeElement($link);

        return $this;
    }

    public function getDom(): ?Dom
    {
        return $this->dom;
    }

    public function setDom(?Dom $dom): self
    {
        $this->dom = $dom;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->status_code;
    }

    public function setStatusCode(?int $status_code): self
    {
        $this->status_code = $status_code;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
