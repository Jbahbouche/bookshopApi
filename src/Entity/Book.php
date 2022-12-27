<?php

namespace App\Entity;

use App\Entity\Review;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookRepository;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Context;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: BookRepository::class)]

#[ApiResource(
    operations: [
    /* new Get(  
        name: 'NotFoundAction',
        controller: NotFoundAction::class, 
        read: false, 
        output: false
    ), */
    new Get,
    //new GetCollection(security: "is_granted('BOOK_READ', object)"),
    //new GetCollection(denormalizationContext: ['groups' => 'getTitle']),
    new GetCollection,
    new GetCollection(order: ['author.name' => 'ASC'], uriTemplate:'/books/all'),
    new Post(security: "is_granted('ROLE_USER')"),
    new Post(name:'publication', uriTemplate:'/books/{id}/publication'),
    new Delete,
    new Put(security: "is_granted('ROLE_ADMIN')", securityMessage: "T'as pas les autorisations")
    //#[Delete(processor: ApiPlatform\Doctrine\Common\State\RemoveProcessor::class)]
    //#[Get(provider: ApiPlatform\Doctrine\Orm\State\ItemProvider::class)]
    ]
)]

#[ApiFilter(OrderFilter::class, properties: ['title'])]
#[ApiFilter(SearchFilter::class, properties: ['description' => 'partial','author.name' => 'partial'])]

/* #[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'price' => 'exact', 'description' => 'partial'])]
http://localhost:8000/api/offers?price=10  renverra toutes les offres avec un prix étant exactement 10. 
http://localhost:8000/api/offers?description=shirt  renverra toutes les offres avec une description contenant le mot "chemise". 
http://localhost:8000/api/offers?product=/api/products/12 renverra toutes les offres avec le produit 12.*/


class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('getTitle')]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'books', targetEntity : Author::class)]
    private ?Author $author = null;

    #[ORM\Column]
    #[Context(normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'd-M-Y'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Review::class)]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setBook($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getBook() === $this) {
                $review->setBook(null);
            }
        }

        return $this;
    }
}
