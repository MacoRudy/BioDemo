<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Depot;
use App\Entity\Producteur;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Faker;
use FakerRestaurant\Provider\fr_FR\Restaurant;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{

    private $em;
    private $mr;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, ManagerRegistry $mr, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->mr = $mr;
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {

        $faker = Faker\Factory::create('fr_FR');
        $faker->addProvider(new Restaurant($faker));

        // Depot

        $depot = [];
        for ($i = 0; $i < 20; $i++) {
            $depot[$i] = new Depot();
            $depot[$i]->setNom($faker->company)
                ->setAdresse($faker->streetAddress)
                ->setCodePostal($faker->randomNumber(5))
                ->setVille($faker->city)
                ->setTelephone($faker->phoneNumber)
                ->setEmail($faker->email);
            $manager->persist($depot[$i]);
        }
        $manager->flush();

        // Categorie

        $sqlComplet = "INSERT INTO `categorie` (`id`, `cat_parent_id`, `nom`) VALUES
(1, NULL, 'OEUFS'),
(2, NULL, 'LEGUMES'),
(3, NULL, 'FRUITS'),
(4, NULL, 'PRODUITS LAITIERS'),
(5, NULL, 'LA BOULANGE'),
(6, NULL, 'PLANTES AROMATIQUES & TISANES'),
(7, NULL, 'AVOINE TRANSFORMEE'),
(8, NULL, 'BOISSONS'),
(9, NULL, 'VIANDE'),
(10, NULL, 'PLANTES'),
(11, NULL, 'TRICOTS & TISSAGES EN MOHAIR'),
(12, NULL, 'GLACES'),
(13, NULL, 'EPICERIE'),
(14, NULL, 'DIVERS'),
(15, 9, 'Boeuf - Veau'),
(16, 9, 'Volaille - Lapin'),
(17, 8, 'Cidre - Apéritif'),
(18, 9, 'Chèvre'),
(19, 9, 'Mouton - Agneau'),
(20, 13, 'Compotes - Confitures - Miel'),
(21, 9, 'Porc'),
(22, 4, 'Yaourts'),
(23, 8, 'Jus de fruits'),
(24, 13, 'Terrines - Pâtés'),
(25, 5, 'Galettes & Crêpes'),
(26, 5, 'Pains'),
(27, 5, 'Brioches'),
(28, 4, 'Lait'),
(29, 4, 'Fromages au lait de vache'),
(30, 4, 'Fromages au lait de chèvre'),
(31, 4, 'Produits laitiers de Bretonne Pie Noir'),
(32, 10, 'Plantes potagères'),
(33, 10, 'Plantes aromatiques et médicinales vivaces'),
(34, 10, 'Fraisiers et rhubarbe'),
(35, 10, 'Plantes aromatiques et fleurs comestibles annuelles'),
(36, 10, 'Ornementales vivaces'),
(37, 10, 'Ornementales annuelles'),
(38, 13, 'Pâtes sèches');
";

        $this->em->getConnection()->executeUpdate($sqlComplet);


        // ProducteuR


        $user = [];
        $producteur = [];
        for ($i = 0; $i < 20; $i++) {

            $nom = $faker->lastName;
            $prenom = $faker->firstName;
            $email = $faker->email;
            $telephone = $faker->phoneNumber;

            $code = strtoupper($nom);
            $siret = $faker->isbn10;

            $adresse = $faker->streetAddress;
            $ville = $faker->city;
            $codePostal = $faker->randomNumber(5);

            $dateInscription = $faker->dateTime;

            $description = $faker->text(100);
            $password = $faker->password;

            $user[$i] = new User();
            $producteur[$i] = new Producteur();


            $user[$i]->setEmail($email)
                ->setPassword($password)
                ->setNom($nom)
                ->setPrenom($prenom)
                ->setAdresse($adresse)
                ->setCodePostal($codePostal)
                ->setVille($ville)
                ->setTelephone($telephone)
                ->setValide(1)
                ->setRoles(['ROLE_PRODUCTEUR'])
                ->setDateInscription($dateInscription);

            $manager->persist($user[$i]);


            $producteur[$i]->setNom($nom)
                ->setCode($code)
                ->setAdresse($adresse)
                ->setCodePostal($codePostal)
                ->setVille($ville)
                ->setTelephone($telephone)
                ->setEmail($email)
                ->setDescription($description)
                ->setSiret($siret)
                ->setUser($user[$i]);

            $manager->persist($producteur[$i]);

        }
        $manager->flush();

        // Creation Admin
        $admin = new User();
        $admin->setEmail('ralpina@hotmail.com')
            ->setPassword($this->passwordEncoder->encodePassword($admin, 'azerty'))
            ->setNom('Macorigh')
            ->setPrenom('Rudy')
            ->setAdresse($faker->streetAddress)
            ->setCodePostal($faker->randomNumber(5))
            ->setVille($faker->city)
            ->setTelephone($faker->phoneNumber)
            ->setValide(1)
            ->setRoles(['ROLE_ADMIN'])
            ->setDateInscription($faker->dateTime);


        $randomDepot = (array)array_rand($depot, rand(1, count($depot)));
        foreach ($randomDepot as $key => $value) {
            $admin->setDepot($depot[$key]);
        }
        $manager->persist($admin);

        $manager->flush();


        // User
        $user = [];
        for ($i = 0;
             $i < 20;
             $i++) {
            $user[$i] = new User();
            $user[$i]->setEmail($faker->email)
                ->setPassword($faker->password)
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setAdresse($faker->streetAddress)
                ->setCodePostal($faker->randomNumber(5))
                ->setVille($faker->city)
                ->setTelephone($faker->phoneNumber)
                ->setValide(1)
                ->setRoles(['ROLE_USER'])
                ->setDateInscription($faker->dateTime);


            $randomDepot = (array)array_rand($depot, rand(1, count($depot)));
            foreach ($randomDepot as $key => $value) {
                $user[$i]->setDepot($depot[$key]);
            }

            $manager->persist($user[$i]);
        }
        $manager->flush();


// Produit
        $categorie = $this->mr
            ->getRepository(Categorie::class)
            ->findSousCategorie();
//        file_put_contents(__DIR__ . '/toto.log', print_r($categorie, true));

        $produit = [];

        for ($i = 0; $i < 100; $i++) {
            $produit[$i] = new Produit();

            if ($i % 3 == 0) {
                $nom = $faker->dairyName();
            } elseif ($i % 2 == 0) {
                $nom = $faker->fruitName();
            } else {
                $nom = $faker->meatName();
            }
            $produit[$i]->setNom($nom)
                ->setPrix($faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 100))
                ->setDescription($faker->text(20))
                ->setReference(strtoupper(substr($nom, 0, 3)));


            $randomProducteur = (array)array_rand($producteur, rand(1, count($producteur)));
            foreach ($randomProducteur as $key => $value) {
                $produit[$i]->setProducteur($producteur[$key]);
            }

            $randomCategorie = (array)array_rand($categorie, rand(1, count($categorie)));
            foreach ($randomCategorie as $key => $value) {
                $produit[$i]->setCategorie($categorie[$key]);

            }
            $manager->persist($produit[$i]);
        }
        $manager->flush();
    }
}
