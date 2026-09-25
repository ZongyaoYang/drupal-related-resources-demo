<?php

declare(strict_types=1);

namespace Drupal\related_resources\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Cache\Cache;

#[Block(
    id: 'related_resources_block',
    admin_label: new TranslatableMarkup('Related Resources'),
    category: new TranslatableMarkup('Custom'),
)]

final class RelatedResourcesBlock extends BlockBase
{
    public function build(): array
    {
        $node = \Drupal::routeMatch()->getParameter('node');

        if (
            !$node instanceof \Drupal\node\NodeInterface
            || $node->bundle() !== 'page'
            || !$node->hasField('field_topic')
            || $node->get('field_topic')->isEmpty()
        ) {
            return [];
        }

        $topic_id = $node->get('field_topic')->target_id;

        $resource_ids = \Drupal::entityQuery('node')
            ->accessCheck(TRUE)
            ->condition('type', 'resource')
            ->condition('status', 1)
            ->condition('field_topic.target_id', $topic_id)
            ->range(0, 5)
            ->execute();

        $resources = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadMultiple($resource_ids);

        $items = [];
        foreach ($resources as $resource) {
            $items[] = [
                '#type' => 'link',
                '#title' => $resource->label(),
                '#url' => $resource->toUrl(),
            ];
        }

        return [
            '#theme' => 'item_list',
            '#items' => $items,
            '#cache' => [
                'contexts' => ['route'],
                'tags' => ['node_list'],
            ],
        ];
    }

    public function getCacheContexts(): array
    {
        return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
    }
}
