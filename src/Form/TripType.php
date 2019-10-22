<?php

namespace App\Form;

use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
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
            ->add('startDate', TextType::class, [
                'label' => 'Date et heure de la sortie'
            ])
            ->add('deadlineDate', TextType::class, [
                'label' => "Date limite d'inscription"
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
