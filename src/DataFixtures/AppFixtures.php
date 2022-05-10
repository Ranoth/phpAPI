<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Pret;
use App\Entity\Livre;
use App\Entity\Adherent;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $manager;
    private $faker;
    private $repoLivre;
    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->faker = Factory::create("fr_FR");
        $this->passwordEncoder=$passwordEncoder;
    }
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->repoLivre = $this->manager->getRepository(Livre::class);
        $this->loadAdherent();
        $this->loadPret();

        $manager->flush();
    }
    public function loadAdherent()
    {
        $genre = ['male', 'female'];
        $commune = ['78003', '78005', '78006', '78007', '78009', '78010', '78320'];
        for ($i = 0; $i < 25; $i++) {
            $adherent = new Adherent();
            $adherent->setNom($this->faker->lastName())
                ->setPrenom($this->faker->firstName($genre[mt_rand(0, 1)]))
                ->setAdresse($this->faker->streetAddress())
                ->setTel($this->faker->phoneNumber())
                ->setCodeCommune($commune[mt_rand(0, sizeof($commune) - 1)])
                ->setMail(strtolower($adherent->getNom()) . "@gmail.com")
                ->setPassword($this->passwordEncoder->hashPassword($adherent, $adherent->getNom()));
            $this->addReference("adherent" . $i, $adherent);
            $this->manager->persist($adherent);
        }
        $adherentAdmin = new Adherent();
        $rolesAdmin[] = ADHERENT::ROLE_ADMIN;
        $adherentAdmin->setNom("Rolland")
        ->setPrenom("Stéphane")
        ->setMail("admin@gmail.com")
        ->setPassword($this->passwordEncoder->hashPassword($adherentAdmin, "Rolland"))
        ->setRoles($rolesAdmin);
        $this->manager->persist($adherentAdmin);
        
        $adherentManager = new Adherent();
        $rolesManager[] = ADHERENT::ROLE_MANAGER;
        $adherentManager->setNom("Durand")
            ->setPrenom("Sophie")
            ->setMail("manager@gmail.com")
            ->setPassword($this->passwordEncoder->hashPassword($adherentManager, "Durand"))
            ->setRoles($rolesManager);
        $this->manager->persist($adherentManager);
        
        $this->manager->flush();
    }
    public function loadPret()
    {
        for ($i = 0; $i < 25; $i++) {
            $max = mt_rand(1, 5);
            for ($j = 0; $j <= $max; $j++) {
                $pret = new Pret();
                $livre = $this->repoLivre->find(mt_rand(1, 49));
                $pret->setLivre($livre)
                    ->setAdherent($this->getReference("adherent" . $i))
                    ->setDatePret($this->faker->dateTimeBetween('-6 months'));
                $dateRetourPrevue = date('Y-m-d H:m:n', strtotime('15 days', $pret->getDatePret()->getTimestamp()));
                $dateRetourPrevue = \DateTime::createFromFormat('Y-m-d H:m:n', $dateRetourPrevue);
                $pret->setDateRetourPrevue($dateRetourPrevue);

                if (mt_rand(1, 3) == 1) {
                    $pret->setDateRetourReelle($this->faker->dateTimeBetween($pret->getDatePret(), "+30 days"));
                }
                $this->manager->persist($pret);
            }
        }
    }
}
