<?php
$Informationsystem_Controller_Show = new Informationsystem_Controller_Show(
    Core_Entity::factory('Informationsystem', 15)
);

$Informationsystem_Controller_Show
    ->xsl(
        Core_Entity::factory('Xsl')->getByName('НовостиНаГлавной')
    )
    ->limit(6)
    ->group(FALSE)
    ->show();
?>


