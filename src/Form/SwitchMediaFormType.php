<?php

namespace App\Form;

use App\Entity\Media;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchMediaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Media $media */
        $media = $options['data'];
        $builder
            ->add('media', EntityType::class, [
                'class' => Media::class,
                'property_path' => 'filename',
                'choices' => $media->getProject()->getMedia(),
                'mapped' => false,
                'data' => $media
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'media' => null
        ]);
    }
}
