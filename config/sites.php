<?php

$countries = array(
  'botswana',
  'brazil',
  'canada',
  'congo',
  'ghana',
  'kenya',
  'indonesia',
  'nigeria',
  'training',
  'uk',
);

$sites = array();
foreach ($countries as $country) {
  $sites["8888.dev.{$country}.dosomething.org"] = $country;
}
