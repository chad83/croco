<?php

namespace App\Entity;

use App\Repository\JobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JobRepository::class)
 * @ORM\Table(indexes={@ORM\Index(columns={"status"})})
 * @ORM\Table(indexes={@ORM\Index(columns={"site"})})
 */
class Job
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $site;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_started;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_finished;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Page::class, mappedBy="job")
     */
    private $pages;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="job")
     */
    private $doms;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $should_force_crawl;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->doms = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->site;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(string $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getDateStarted(): ?\DateTimeInterface
    {
        return $this->date_started;
    }

    public function setDateStarted(\DateTimeInterface $date_started): self
    {
        $this->date_started = $date_started;

        return $this;
    }

    public function getDateFinished(): ?\DateTimeInterface
    {
        return $this->date_finished;
    }

    public function setDateFinished(?\DateTimeInterface $date_finished): self
    {
        $this->date_finished = $date_finished;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Dom[]
     */
    public function getDoms(): Collection
    {
        return $this->doms;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setJob($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getJob() === $this) {
                $page->setJob(null);
            }
        }

        return $this;
    }

    public function getShouldForceCrawl(): ?bool
    {
        return $this->should_force_crawl;
    }

    public function setShouldForceCrawl(?bool $should_force_crawl): self
    {
        $this->should_force_crawl = $should_force_crawl;

        return $this;
    }
}
