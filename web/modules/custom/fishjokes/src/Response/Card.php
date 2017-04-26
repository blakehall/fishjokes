<?php

namespace Alexa\Response;

class Card {
  public $type = '';
  public $title = '';
  public $text = '';
  // 720w x 480h
  public $smallImageUrl = '';
  // 1200w x 800h
  public $largeImageUrl = '';

  public function render() {
    return array(
      'type' => $this->type,
      'title' => $this->title,
      'text' => $this->text,
      'image' => array(
        "smallImageUrl" => $this->smallImageUrl,
        "largeImageUrl" => $this->largeImageUrl
      )
    );
  }
}