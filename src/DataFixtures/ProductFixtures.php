<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US'); 

        $brands = ['Apex', 'Novatech', 'Skyline', 'Northwind', 'BluePeak', 'ZenWave', 'Lumina', 'Quantix'];
        $stockStatuses = ['in_stock', 'limited', 'preorder', 'out_of_stock'];
        $oses = ['Android 14', 'Android 13', 'HarmonyOS', 'Windows 11', 'Linux', 'iOS 18', 'ChromeOS'];
        $colors = ['Black', 'White', 'Midnight Blue', 'Graphite', 'Silver', 'Gold', 'Olive', 'Crimson'];
        $resolutions = ['1920x1080', '2340x1080', '2400x1080', '2560x1440', '2732x2048', '3840x2160'];
        $batteries = ['4000 mAh', '4500 mAh', '5000 mAh', '6000 mAh', '42 Wh', '60 Wh'];
        $cameras = ['12 MP', '48 MP', '50 MP', '64 MP', '108 MP', '12 MP + 8 MP ultrawide'];
        $screenSizes = ['5.8"', '6.1"', '6.5"', '6.7"', '13.3"', '14"', '27"'];
        
        $presets = [
            [
                'name' => 'Nova Phone X',
                'brand' => 'Novatech',
                'model' => 'X (2025)',
                'description' => 'A sleek 6.1" smartphone with long-lasting battery and a bright OLED display.',
                'price' => '699.00',
                'currency' => 'USD',
                'releaseDate' => new \DateTime('2025-03-15'),
                'stockStatus' => 'in_stock',
                'os' => 'Android 14',
                'color' => 'Graphite',
                'screenSize' => '6.1"',
                'resolution' => '2400x1080',
                'battery' => '5000 mAh',
                'camera' => '50 MP',
                'weight' => '172 g',
                'dimensions' => '146.7 x 71.5 x 7.8 mm',
                'imageUrl' => 'https://picsum.photos/seed/novaphonex/800/600',
            ],
            [
                'name' => 'Lumina Tab Pro',
                'brand' => 'Lumina',
                'model' => 'Pro 13',
                'description' => 'A lightweight 13.3" productivity tablet with stylus support.',
                'price' => '949.00',
                'currency' => 'USD',
                'releaseDate' => new \DateTime('2024-11-02'),
                'stockStatus' => 'limited',
                'os' => 'ChromeOS',
                'color' => 'Silver',
                'screenSize' => '13.3"',
                'resolution' => '2732x2048',
                'battery' => '42 Wh',
                'camera' => '12 MP',
                'weight' => '612 g',
                'dimensions' => '285 x 210 x 6.3 mm',
                'imageUrl' => 'https://picsum.photos/seed/luminatabpro/800/600',
            ],
        ];

        foreach ($presets as $p) {
            $product = (new Product())
                ->setName($p['name'])
                ->setBrand($p['brand'])
                ->setModel($p['model'])
                ->setDescription($p['description'])
                ->setPrice($p['price'])
                ->setCurrency($p['currency'])
                ->setReleaseDate($p['releaseDate']) // DateTime (mutable)
                ->setStockStatus($p['stockStatus'])
                ->setOs($p['os'])
                ->setColor($p['color'])
                ->setScreenSize($p['screenSize'])
                ->setResolution($p['resolution'])
                ->setBattery($p['battery'])
                ->setCamera($p['camera'])
                ->setWeight($p['weight'])
                ->setDimensions($p['dimensions'])
                ->setImageUrl($p['imageUrl'])
                ->setCreatedAt(new \DateTimeImmutable('-6 months'))
                ->setUpdatedAt(new \DateTimeImmutable('-1 months'));

            $manager->persist($product);
        }

        // random
        for ($i = 0; $i < 18; $i++) {
            $brand = $faker->randomElement($brands);
            $modelWord = ucfirst($faker->bothify('##?')); // ex: "42K"
            $family = $faker->randomElement(['Phone', 'Tab', 'Watch', 'Book', 'Display']);
            $name = sprintf('%s %s %s',(string) $brand,(string) $family,(string) $faker->randomElement(['Air', 'Plus', 'Pro', 'Max', 'Mini', 'Ultra']));

            $price = number_format($faker->randomFloat(2, 59, 2499), 2, '.', '');

            $releasedAt = $faker->dateTimeBetween('-3 years', 'now'); // DateTime (mutable)
            $createdAt = $faker->dateTimeBetween('-18 months', '-1 months'); // DateTime
            $updatedAt = $faker->dateTimeBetween($createdAt, 'now');         // DateTime

            $product = (new Product())
                ->setName($name)
                ->setBrand((string) $brand)
                ->setModel($modelWord)
                ->setDescription((string) $faker->paragraphs($faker->numberBetween(1, 3), true)) // anglais
                ->setPrice($faker->boolean(90) ? $price : null) // parfois null
                ->setCurrency((string) $faker->randomElement(['USD', 'EUR', 'GBP', null]))
                ->setReleaseDate($releasedAt) // Types::DATE_MUTABLE ⇒ DateTime
                ->setStockStatus((string) $faker->randomElement($stockStatuses))
                ->setOs((string) $faker->randomElement($oses))
                ->setColor((string) $faker->randomElement($colors))
                ->setScreenSize((string) $faker->randomElement($screenSizes))
                ->setResolution((string) $faker->randomElement($resolutions))
                ->setBattery((string) $faker->randomElement($batteries))
                ->setCamera((string) $faker->randomElement($cameras))
                ->setWeight($faker->boolean() ? $faker->numberBetween(120, 250) . ' g' : $faker->randomFloat(1, 0.9, 2.8) . ' kg')
                ->setDimensions(sprintf(
                    '%s x %s x %s mm',
                    $faker->randomFloat(1, 120, 320),
                    $faker->randomFloat(1, 60, 240),
                    $faker->randomFloat(1, 5, 25)
                ))
                ->setImageUrl(sprintf('https://picsum.photos/seed/%s/800/600', $faker->uuid()))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($updatedAt));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
