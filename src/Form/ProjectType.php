<?php

namespace App\Form;

use App\Entity\Marker;
use App\Entity\Project;
use App\Repository\MarkerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $project = $options['data'];

        $builder
            ->add('code')
            ->add('base_path')
            ->add('honoree_name')
            ->add('honoree_title')
            ->add('music') // https://www.bensound.com/
            ->add('signs')
            ->add('last_marker',EntityType::class, [
                // 'choice_attr' => 'note',
                'expanded' => true,
                'class' => Marker::class,
                'choice_label' => 'note',
                'query_builder' => function (MarkerRepository $er) use ($project) {
                    return $er
                        ->createQueryBuilder('marker')
                        ->where('marker.media IN (:media)')
                        ->andWhere('marker.irrelevant <> true')
                        ->andWhere('marker.hidden <> true')
                        ->setParameter('media', $project->getMedia())
                        ->orderBy('marker.idx', 'ASC');

                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
