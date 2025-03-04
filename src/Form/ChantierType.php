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

class ChantierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lieu')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('status')
            ->add('competences', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nomCompetence',
                'multiple' => true,
                'expanded' => true, 
                'mapped' => false, 
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer le chantier',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chantier::class,
        ]);
    }
}

