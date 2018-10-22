<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filename')
            ->add('path')
            ->add('flacExists')
            ->add('transcriptJson')
            ->add('transcriptRequested')
            ->add('word_count')
            ->add('file_size')
            ->add('duration')
            ->add('speaker')
            ->add('display')
            ->add('marking')
            ->add('lastTransitionTime')
            ->add('project')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
