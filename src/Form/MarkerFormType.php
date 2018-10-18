<?php

namespace App\Form;

use App\Entity\Marker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('note')
            ->add('color')
            ->add('idx')
            ->add('firstWordIndex')
            ->add('lastWordIndex')
            ->add('media', null, [
                'disabled' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Marker::class,
        ]);
    }
}
