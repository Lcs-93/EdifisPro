<?php
namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Competence;
use App\Entity\CompetenceChantier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChantierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lieu')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'En cours' => 'en_cours',
                    'En pause' => 'en_pause',
                    'Terminé' => 'termine',
                ],
                'expanded' => false, // Affiche un menu déroulant
                'multiple' => false, // Permet de ne choisir qu'une seule option
                'attr' => ['class' => 'form-select'] // Bootstrap stylise le select
            ])
            ->add('competences', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nomCompetence',
                'multiple' => true,
                'expanded' => true, 
                'mapped' => false, 
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chantier::class,
        ]);
    }
}

