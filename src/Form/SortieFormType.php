<?php


namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [

            ])
            ->add('dateHeuredebut', DateTimeType::class, [

                'widget' => 'single_text', // permet à l'utilisateur de sélectionner une date et une heure
            ])
            ->add('duree', IntegerType::class, [

            ])
            ->add('dateDebutInscription', DateTimeType::class, [
                'widget' => 'single_text',


            ])
            ->add('dateLimiteInscription', DateTimeType::class, [

                'widget' => 'single_text',
            ])
            ->add('nmInscriptionMax', IntegerType::class, [

            ])

            ->add('infoSortie', TextareaType::class, [

            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',

            ]);





    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
