<?php

namespace Drupal\examplemodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SampleBlock' block.
 *
 * @Block(
 * id = "sample_block",
 * admin_label = @Translation("Sample block"),
 * )
 */

class SampleBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        return array(
            '#theme' => "sample_block",
            '#title' => 'puta',
            "#description" => "a ningú li interessa aquest projecte",
            "#votes" => "34",
            //'#description' => 'Lorem ipsum dolar sum amet ..'
        );
    }
}