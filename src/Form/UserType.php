<?php

namespace App\Form;

use App\Entity\Competence;
use App\Entity\CompetenceUser;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit']; // Vérifier si on est en mode édition
        $generatedPassword = $options['generated_password'] ?? ''; // Récupérer le MDP généré
        $isAssignedToEquipe = $options['is_assigned_to_team']; // Vérifier si l'utilisateur est assigné à une équipe

        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('plainPassword', $isEdit ? PasswordType::class : TextType::class, [
                'mapped' => false, // Ne pas mapper ce champ à l'entité User
                'required' => !$isEdit, // Obligatoire uniquement en création
                'attr' => [
                    'autocomplete' => 'new-password',
                    'value' => $generatedPassword, // Pré-remplir avec le MDP généré
                ],
                'label' => $isEdit ? 'Nouveau mot de passe' : 'Mot de passe',
                'constraints' => $isEdit ? [] : [
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
                // Si l'utilisateur est assigné à une équipe, désactiver le champ
                'disabled' => $isAssignedToEquipe,
            ]);

        // Ajouter un écouteur d'événement pour pré-remplir le champ competences
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

            // Forcer la mise à jour du champ competences
            $form->add('competences', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nomCompetence',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'required' => false,
                'data' => $competences, // Ajouter directement les compétences sélectionnées
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false, // Option pour savoir si on est en édition
            'generated_password' => '', // Option pour stocker le MDP généré
            'is_assigned_to_team' => false, // Option pour savoir si l'utilisateur est assigné à une équipe
        ]);
    }
}
