<?php


namespace App\Service;


use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;


class ProductService
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        $this->entityManager = $entityManager;
        $this->doctrine = $doctrine;
    }

    public function newProduct(string $name, int $quantity, bool $isActive)
    {
        $product = new Product();
        $product->setName($name);
        $product->setQuantity($quantity);
        $product->setIsActive($isActive);


        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return $product;
    }

    public function getProduct($product_id)
    {
        // Product control
        $entityManager = $this->doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($product_id);

        if (!$product) {
            throw new Exception('Product not found');
        }

        if($product->isIsActive() != 1)
        {
            throw new Exception('Product is not active');
        }

        return $product;
    }

}