<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders_article')]
class OrdersArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'orders_id', type: 'integer', nullable: true)]
    private ?int $ordersId = null;

    #[ORM\Column(name: 'article_id', type: 'integer', nullable: true)]
    private ?int $articleId = null;

    #[ORM\Column(name: 'amount', type: 'float')]
    private float $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdersId(): ?int
    {
        return $this->ordersId;
    }

    public function setOrdersId(?int $ordersId): void
    {
        $this->ordersId = $ordersId;
    }

    public function getArticleId(): ?int
    {
        return $this->articleId;
    }

    public function setArticleId(?int $articleId): void
    {
        $this->articleId = $articleId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}
