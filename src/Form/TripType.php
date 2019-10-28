<?php

namespace App\Form;

use App\Entity\Trip;
use App\Entity\TripPlace;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('duration')
            ->add('deadlineDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('maxRegistrationNumber')
            ->add('description')
            ->add('place', EntityType::class, [
                'class' => TripPlace::class,
                'choice_label' => 'name',
                'placeholder' => 'choisir un lieu'
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
