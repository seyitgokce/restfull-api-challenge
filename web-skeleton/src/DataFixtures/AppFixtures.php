<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $faker;

    private $passwordEncoder;
    /**
     * @var UserService
     */
    private $service;

    public function __construct(UserService $service)
    {
        $this->faker = Factory::create();
        $this->service = $service;
    }

    public function load(ObjectManager $manager)
    {
        $this->addUsers($manager);
        $this->addProducts($manager);
    }

    private function addUsers(ObjectManager $manager)
    {
        for($i = 1; $i <= 3; $i++){
            $this->service->newUser(
                "info@company{$i}.com",
                "company{$i}"
            );
        }
    }

    private function addProducts(ObjectManager $manager)
    {
        $user = $manager->getRepository(User::class)->findOneBy([]);
        // Dummy data
        for ($i = 1; $i <= 100; $i++) {
            $product = new Product();
            $product->setName($this->faker->sentence());
            $product->setProductCode($this->faker->word);
            $product->setName($this->faker->sentence());
            $product->setDescription($this->faker->text());
            $product->setPrice($this->faker->randomFloat());
            $product->setQuantity(rand(0,2000));
            $product->setIsActive(rand(0,1));

            $manager->persist($product);

            if ($i === 1) {
                $this->addOrder($manager, $user, $product);
            }
        }

        $manager->flush();
    }

    private function addOrder(ObjectManager $manager, $user, Product $product)
    {
        $quantity = rand(1, 50);

        $order = new Order();
        $order->setUser($user);
        $order->setProduct($product);
        $order->setOrderCode($this->faker->word);
        $order->setQuantity($quantity);
        $order->setAddress($this->faker->text());
        $order->setPrice($quantity * $product->getPrice());
        $order->setOrderDate(new \DateTime('now'));
        $order->setShippingAddress($this->faker->text());
        $order->setShippingDate(new \DateTime('tomorrow'));

        $manager->persist($order);
        $manager->flush();
    }
}
