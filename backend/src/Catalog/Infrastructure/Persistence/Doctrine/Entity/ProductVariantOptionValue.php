<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_variant_option_values')]
#[ORM\UniqueConstraint(name: 'uniq_product_variant_option_values_pair', columns: ['variant_id', 'option_value_id'])]
#[ORM\Index(name: 'idx_product_variant_option_values_variant', columns: ['variant_id'])]
#[ORM\Index(name: 'idx_product_variant_option_values_value', columns: ['option_value_id'])]
class ProductVariantOptionValue
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ProductVariant $variant;

    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class)]
    #[ORM\JoinColumn(name: 'option_value_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ProductOptionValue $optionValue;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, ProductVariant $variant, ProductOptionValue $optionValue)
    {
        $this->id = $id;
        $this->variant = $variant;
        $this->optionValue = $optionValue;
        $this->createdAt = new DateTimeImmutable();
    }
}
