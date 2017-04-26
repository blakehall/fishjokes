<?php

namespace Drupal\fishjokes\EventSubscriber;

use Drupal\alexa\AlexaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\fishjokes\Response\Card;

/**
 * Class RequestSubscriber.
 *
 * @package Drupal\fishjokes
 */
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * Gets the event.
   */
  public static function getSubscribedEvents() {
    // TODO: Implement getSubscribedEvents() method.
    $events['alexaevent.request'][] = array('onRequest', 0);
    return $events;
  }


  /**
   * Called upon a request event.
   *
   * @param \Drupal/alexa\AlexaEvent $event
   *    The event object.
   */
  public function onRequest(AlexaEvent $event) {
    $request = $event->getRequest();
    $response = $event->getResponse();

    switch($request->intentName) {
      case 'AMAZON:HelpIntent':
        $response->respond('You can ask anything and I will respond with a fish joke.');
        break;

      default:
        // $request->intentName = 'GetCategorizedJokes';
        // See if we're getting a category specified by a slot from our Alexa request.
        $term_name = isset($request->slots['Category']) ? $request->slots['Category'] : NULL;

        $query = \Drupal::entityQuery('node');
        $joke_query = $query->condition('status', 1)
          ->condition('type', 'joke');
        // If the slot in the Alexa request is filled, include it as a filter in our query.
        if ($term_name) {
          $joke_query->condition('field_tags.entity.name', $term_name, 'IN');
        }

        $joke_nids = $joke_query->execute();
        // If we didn't find anything with our EntityQuery let's just use node 7.
        $joke_nid = count($joke_nids) === 0 ? 7 : $joke_nids[array_rand($joke_nids)];
        $joke = \Drupal\node\Entity\Node::load($joke_nid);
        $title = $joke->title->value;
        $punchline = $joke->field_punchline->value;
        $text = $title .' ' .$punchline;

        $image_file_uri = ($joke->field_image->entity) ? $joke->field_image->entity->getFileUri() : 'public://2017-04/Shark_GreatWhiteClownMO.jpg';
        $smallImageUrl = file_create_url($image_file_uri);

        // Build the card format expected by the Alexa library,
        // but use our custom Standard Card implementation.
        $card = new Card;
        $card->type = 'Standard';
        $card->title = $joke->title->value;
        $card->text = $text;
        $card->content = $text;
        $card->smallImageUrl = $smallImageUrl;
        $card->largeImageUrl = $smallImageUrl;
        $response->card = $card;
        // Respond by reading the text of the joke and end the session.
        $response->respond($text)
            ->endSession();
        break;
    }
  }
}
