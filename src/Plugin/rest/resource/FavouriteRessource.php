<?php

namespace Drupal\pagedesigner_favourite\Plugin\rest\resource;

use Drupal\user\UserData;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "pagedesigner_favourite",
 *   label = @Translation("Pagedesigner favourite"),
 *   uri_paths = {
 *     "canonical" = "/pagedesigner/favourite/{id}",
 *     "create" = "/pagedesigner/favourite",
 *   }
 * )
 */
class FavouriteRessource extends ResourceBase {


  /**
   * The storage key.
   *
   * @var string
   */
  public const KEY = 'pagedesigner_favourite';

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserData
   */
  protected $userData;
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ElementResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \Drupal\user\UserData $user_data
   *   The user data service.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        array $serializer_formats,
        LoggerInterface $logger,
        AccountProxyInterface $current_user,
        UserData $user_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->userData = $user_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->getParameter('serializer.formats'),
          $container->get('logger.factory')->get('pagedesigner'),
          $container->get('current_user'),
          $container->get('user.data')
      );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   If the request is malformed.
   */
  public function get($id = NULL) {
    $patterns = json_decode($this->userData->get(static::KEY, $this->currentUser->id(), static::KEY), TRUE);
    if (!empty($id)) {
      if (!empty($patterns[$id])) {
        $response = new ResourceResponse([TRUE], 200);
      }
      else {
        $response = new ResourceResponse([FALSE], 200);
      }
    }
    else {
      $response = new ResourceResponse([$patterns], 200);
    }
    $response->addCacheableDependency(['cache' => ['max-age' => 0]]);
    return $response;
  }

  /**
   * Responds to POST requests.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   If the request is malformed.
   */
  public function post($request) {
    if (empty($request['id'])) {
      throw new BadRequestHttpException('The id is mandatory for the post requests.');
    }
    $pattern = $request['id'];
    $patterns = json_decode($this->userData->get(static::KEY, $this->currentUser->id(), static::KEY), TRUE);
    $patterns[$pattern] = $pattern;
    $this->userData->set(static::KEY, $this->currentUser->id(), static::KEY, json_encode($patterns));
    $response = new ModifiedResourceResponse([$pattern], 201);
    return $response;
  }

  /**
   * Responds to DELETE requests.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   If the request is malformed.
   */
  public function delete($id = NULL) {
    if (empty($id)) {
      throw new BadRequestHttpException('The id is mandatory for the delete requests.');
    }
    $response = new ResourceResponse(NULL, 404);
    $patterns = json_decode($this->userData->get(static::KEY, $this->currentUser->id(), static::KEY), TRUE);
    if (!empty($patterns[$id])) {
      unset($patterns[$id]);
      $this->userData->set(static::KEY, $this->currentUser->id(), static::KEY, json_encode($patterns));
      $response = new ModifiedResourceResponse(NULL, 204);
    }
    return $response;
  }

}
