<?php

namespace App\Form;

use App\Entity\Trip;
use App\Entity\TripPlace;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('startDate')
            ->add('duration')
            ->add('deadlineDate')
            ->add('maxRegistrationNumber')
            ->add('description')
            ->add('place', EntityType::class, [
                'class' => TripPlace::class,
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
