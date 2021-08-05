<?php

namespace Drupal\mars_common\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BlockAjaxController is responsible for Ajax block callback logic.
 *
 * @package Drupal\mars_common\Controller
 */
class BlockAjaxController extends ControllerBase {

  /**
   * Block manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockManager $block_manager, RendererInterface $renderer) {
    $this->blockManager = $block_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('renderer')
    );
  }

  /**
   * Implements ajax block update request handler.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function ajaxBlock(Request $request) {
    $plugin_id = $request->get('plugin_id', '');
    $configuration = $request->get('block_config', []);

    // Construct and render the block.
    $plugin_block = $this->blockManager->createInstance($plugin_id);
    $plugin_block->setConfiguration($configuration);
    $block = $plugin_block->build();

    // Create the response.
    $response = new AjaxResponse();

    // Render the form using the renderer service, with the renderRoot method.
    $rendered_form = $this->renderer->renderRoot($block);

    // Add any attachments for the component to the response.
    $response->addAttachments($block['#attached']);

    // Add the ajax command to the response.
    $response->addCommand(new InsertCommand(
      '[data-ajax-block-id="' . $configuration['ajaxId'] . '"]',
      $rendered_form
    ));
    return $response;
  }

}
