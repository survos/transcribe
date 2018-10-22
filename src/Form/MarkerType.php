<?php

namespace App\Form;

use App\Entity\Marker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('note')
            ->add('color')
            ->add('idx')
            ->add('first_word_index')
            ->add('last_word_index')
            ->add('irrelevant')
            ->add('hidden')
            ->add('startTime')
            ->add('endTime')
            ->add('media')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Marker::class,
        ]);
    }
}
