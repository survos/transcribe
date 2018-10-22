<?php

namespace App\Form;

use App\Entity\Marker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $colors = ['red', 'cyan', 'green'];
        $builder
            ->add('title')
            ->add('note')
            ->add('color', ChoiceType::class, [
                'choices' => array_combine($colors, $colors)
            ])
            // ->add('idx')
            ->add('firstWordIndex', HiddenType::class)
            ->add('lastWordIndex', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Marker::class,
        ]);
    }
}
