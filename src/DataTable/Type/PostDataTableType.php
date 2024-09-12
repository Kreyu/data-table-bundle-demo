<?php

declare(strict_types=1);

namespace App\DataTable\Type;

use App\DataTable\Column\Extension\DefaultLabelActionsColumnTypeExtension;
use App\DataTable\Column\Extension\TruncateTextColumnTypeExtension;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\PostRepository;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\FormActionType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\EntityFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\StringFilterType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostDataTableType extends AbstractDataTableType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('title', TextColumnType::class, [
                /**
                 * By default, label is "guessed" from the column name in sentence case.
                 * In this case, the label would be "Title", but we're changing it to "label.title".
                 *
                 * If column name consists of multiple words, e.g. "fullName", the label would be "Full name".
                 *
                 * Unless the "header_translation_domain" is set to false, the label will be translated.
                 */
                'label' => 'label.title',
                'sort' => true,
            ])
            ->addColumn('summary', TextColumnType::class, [
                'label' => 'label.summary',
                /** This option is available thanks to the {@link TruncateTextColumnTypeExtension}. */
                'truncate' => true,
                'sort' => true,
                'value_attr' => [
                    'style' => 'max-width: 20rem;'
                ],
            ])
            ->addColumn('author', LinkColumnType::class, [
                'label' => 'label.author',
                'formatter' => function (User $author): string {
                    return sprintf('%s (%s)', $author->getFullName(), $author->getUsername());
                },
                /**
                 * Just for example, we're displaying the author as link.
                 * This link will redirect to the same page, but with author filter applied with selected author.
                 */
                'href' => function (User $author) use ($builder) {
                    return $this->generateUrlWithAppliedFilter($builder, 'author', $author->getId());
                },
                /**
                 * In this case, we want to sort by the authors' full name and username at the same time.
                 * There's two commonly used methods to handle it:
                 *
                 * 1) use CONCAT DQL function as the 'sort' option value, e.g. 'sort' => 'CONCAT(a.fullName, a.username)'
                 * 2) add the concatenated value as hidden select to the query builder passed to the data table, and use its alias
                 *
                 * In this example we're using second method, and the CONCAT is added in {@link PostRepository::createByAuthorQueryBuilder()}:
                 *
                 * ```
                 * $queryBuilder->addSelect('CONCAT(a.fullName, a.username) AS HIDDEN authorFullNameWithUsername')
                 * ```
                 */
                'sort' => 'authorFullNameWithUsername',
            ])
            ->addColumn('tags', CollectionColumnType::class, [
                'label' => 'label.tags',
                'entry_type' => LinkColumnType::class,
                'entry_options' => [
                    'property_path' => 'name',
                    /**
                     * Just for example, we're displaying each tag as link.
                     * This link will redirect to the same page, but with tags filter applied with selected tag.
                     */
                    'href' => function (string $name, Tag $tag) use ($builder) {
                        return $this->generateUrlWithAppliedFilter($builder, 'tag', $tag->getId());
                    }
                ],
            ])
            ->addColumn('publishedAt', DateTimeColumnType::class, [
                'label' => 'label.published_at',
                'sort' => true,
            ])
        ;

        /**
         * Where's the actions' column?
         *
         * By default, the column is added automatically if at least one **row** action
         * is defined for the data table. This feature can be disabled by using:
         *
         * ```
         * $builder->setAutoAddingActionsColumn(false);
         * ```
         *
         * You can add this column manually by using the ActionsColumnType type:
         *
         * @link https://data-table-bundle.swroblewski.pl/reference/types/column/actions
         *
         * To globally change the default label to 'label.actions' of this column, we're using an extension:
         *
         * @link DefaultLabelActionsColumnTypeExtension
         */

        $builder
            ->addAction('new', ButtonActionType::class, [
                'label' => 'action.create_post',
                'href' => $this->urlGenerator->generate('admin_post_new'),
                'attr' => ['class' => 'btn btn-success'],
                'icon_attr' => ['class' => 'fa fa-plus'],
            ])
        ;

        $builder
            ->addRowAction('show', ButtonActionType::class, [
                'label' => false,
                'attr' => ['class' => 'btn btn-sm btn-secondary'],
                'icon_attr' => ['class' => 'fa fa-eye mr-0'],
                'href' => function (Post $post): string {
                    return $this->urlGenerator->generate('admin_post_show', ['id' => $post->getId()]);
                },
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'label' => false,
                'attr' => ['class' => 'btn btn-sm btn-primary'],
                'icon_attr' => ['class' => 'fa fa-edit mr-0'],
                'href' => function (Post $post): string {
                    return $this->urlGenerator->generate('admin_post_edit', ['id' => $post->getId()]);
                },
            ])
            ->addRowAction('delete', FormActionType::class, [
                'label' => false,
                'button_attr' => ['class' => 'btn btn-sm btn-danger'],
                'icon_attr' => ['class' => 'fa fa-trash mr-0'],
                'method' => 'POST',
                'action' => function (Post $post): string {
                    return $this->urlGenerator->generate('admin_post_delete', ['id' => $post->getId()]);
                },
                /**
                 * Dangerous actions can require confirmation by the user.
                 *
                 * This can be enabled by setting the 'confirmation' option to true
                 * or an array with additional configuration.
                 *
                 * @link https://data-table-bundle.swroblewski.pl/docs/components/actions#adding-action-confirmation
                 */
                'confirmation' => true,
            ])
        ;

        $builder
            ->addFilter('title', StringFilterType::class, [
                'label' => 'label.title',
            ])
            ->addFilter('summary', StringFilterType::class, [
                'label' => 'label.summary',
            ])
            ->addFilter('author', EntityFilterType::class, [
                'label' => 'label.author',
                'form_options' => [
                    'class' => User::class,
                    // This 'choice_label' is used by the form, not data table! See below.
                    'choice_label' => 'fullName',
                ],
                // This 'choice_label' is used by the data table and represents a field
                // used to render the selected author as active filter button.
                'choice_label' => 'fullName',
            ])
            ->addFilter('tag', EntityFilterType::class, [
                'label' => 'label.tags',
                'query_path' => 't2.id',
                'form_options' => [
                    'class' => Tag::class,
                ],
            ])
        ;

        /**
         * Default sorting can be applied using the 'setDefaultSortingData()' method.
         *
         * Any sorting applied to the query builder outside the data table will be overwritten.
         * The key in the sorting data should equal to the column name defined in the data table.
         * If the data table column of given name does not exist, the sorting will be ignored.
         *
         * @see https://data-table-bundle.swroblewski.pl/docs/features/sorting#default-sorting
         */
        $builder->setDefaultSortingData(SortingData::fromArray([
           'publishedAt' => 'desc',
        ]));

        /**
         * It is possible to apply HTML attributes on header and value rows directly from the data table type.
         *
         * In this example, we're adding a slightly orange-ish background color to the value row
         * if the post has scheduled publication (its 'publishedAt' is a future date).
         */
        $builder->setValueRowAttribute('style', function (Post $post) {
            return $post->isScheduled() ? 'background-color: #fef5e6' : '';
        });
    }

    private function generateUrlWithAppliedFilter(DataTableBuilderInterface $builder, string $filter, mixed $value): string
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            throw new \LogicException('Unable to retrieve current request.');
        }

        $route = $request->attributes->getString('_route');

        $params = array_merge_recursive($request->query->all(), $request->attributes->get('_route_params'));
        $params[$builder->getFiltrationParameterName()][$filter]['value'] = $value;

        return $this->urlGenerator->generate($route, $params);
    }
}