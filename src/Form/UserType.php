<?php

namespace App\Form;

use App\Entity\Competence;
use App\Entity\CompetenceUser;
use App\Entity\Role;
use App\Entity\User;
use PhpParser\Node\Expr\Array_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
	        ->add('plainPassword', PasswordType::class, [
		        'mapped' => false,
		        'constraints' => [
			        new NotBlank([
				        'message' => 'Veuillez entrer un mot de passe',
			        ]),
		        ],
	        ])
	        ->add('roles', ChoiceType::class, [
		        'choices' => [
			        'Administrateur' => 'ROLE_ADMIN',
			        'Utilisateur' => 'ROLE_USER',
		        ],
		        'expanded' => true,
		        'multiple' => true,
	        ])
	        ->add('competences', EntityType::class, [
		        'class' => Competence::class,
		        'choice_label' => 'nomCompetence',
		        'multiple' => true,
		        'expanded' => true,
		        'mapped' => false, // Géré manuellement dans le contrôleur
		        'required' => false,
	        ]);

	    // 🔥 Ajouter un écouteur d'événement pour pré-remplir le champ competences
	    $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
		    $user = $event->getData();
		    $form = $event->getForm();

		    if (!$user || null === $user->getId()) {
			    return;
		    }

		    // Récupérer les compétences associées via CompetenceUser
		    $competences = [];
		    foreach ($user->getCompetenceUsers() as $competenceUser) {
			    $competences[] = $competenceUser->getCompetence();
		    }

		    // 🔍 Debug : Vérifier les compétences récupérées
		    dump($competences);

		    // 🔥 Forcer la mise à jour du champ competences
		    $form->add('competences', EntityType::class, [
			    'class' => Competence::class,
			    'choice_label' => 'nomCompetence',
			    'multiple' => true,
			    'expanded' => true,
			    'mapped' => false,
			    'required' => false,
			    'data' => $competences, // 🔥 Ajout direct des compétences sélectionnées
		    ]);
	    });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
