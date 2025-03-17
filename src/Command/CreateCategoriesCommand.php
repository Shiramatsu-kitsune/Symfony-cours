<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-categories',
    description: 'Crée les 3 catégories par défaut pour les articles.',
)]
class CreateCategoriesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $noms = [
            'Annonce Créateurs de Contenus',
            'Annonce Entreprise',
            'Annonce Artiste'
        ];

        foreach ($noms as $nom) {
            $categorie = new Categorie();
            $categorie->setNom($nom);
            $this->entityManager->persist($categorie);
        }

        $this->entityManager->flush();

        $output->writeln('<info> Catégories créées avec succès !</info>');

        return Command::SUCCESS;
    }
}
