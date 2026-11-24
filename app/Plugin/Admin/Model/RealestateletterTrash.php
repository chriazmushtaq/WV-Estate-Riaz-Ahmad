<?php
class RealestateletterTrash extends AppModel
{
    public $useTable = 'real_estate_letters_trash';
    public $validationDomain = 'validation';
    public $actsAs = array('search-master.Searchable');
}
?>
