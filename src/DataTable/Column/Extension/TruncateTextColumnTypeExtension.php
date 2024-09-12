<?php

declare(strict_types=1);

namespace App\DataTable\Column\Extension;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Extension\AbstractColumnTypeExtension;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TruncateTextColumnTypeExtension extends AbstractColumnTypeExtension
{
    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        if (false === $options['truncate']) {
            return;
        }

        array_unshift($view->vars['block_prefixes'], 'text_truncate');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('truncate', false)
            ->setAllowedTypes('truncate', 'bool')
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [TextColumnType::class];
    }
}
