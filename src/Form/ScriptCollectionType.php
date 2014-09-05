<?php

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScriptCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('scripts', 'collection', array(
            'type' => new ScriptType(),
            'allow_add' => true,
            'prototype' => true,
            'prototype_name' => '__idx__'
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Form\Model\ScriptCollection',
        ));
    } 
    
    public function getName()
    {
        return 'script_collection_type';
    }
}