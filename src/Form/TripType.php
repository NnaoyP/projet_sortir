<?php

namespace App\Form;

use App\Entity\Trip;
use App\Entity\TripPlace;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom de la sortie'
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'choice'
            ])
            ->add('deadlineDate', DateType::class, [
                'widget' => 'choice'
            ])
            ->add('maxRegistrationNumber', null, [
                'label' => "Nombre de place"
            ])
            ->add('duration', null, [
                'label' => "Durée"
            ])
            ->add('description',null, [
                'label' => "Description et infos"
            ])
            ->add('place', EntityType::class, [
                'label' => 'Lieu',
                'class' => TripPlace::class,
                'choice_label' => 'fullname'
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
