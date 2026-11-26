<?php

namespace App\Form;

use App\Entity\ChallengeCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Le filtre de la liste des défis : une catégorie, ou toutes.
 * Envoyé en GET (le filtre se lit dans l'adresse), donc sans jeton CSRF.
 */
class ChallengeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => ChallengeCategory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'required' => false,
                'expanded' => true,
                'placeholder' => 'Toutes',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
