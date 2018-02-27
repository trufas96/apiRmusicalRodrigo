<?php 

class Model_Songs extends Orm\Model
{
    protected static $_table_name = 'Songs';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id',
        'title' => array(
            'data_type' => 'text'   
        ),
        'url' => array(
            'data_type' => 'text'   
        )
    );
    protected static $_many_many = array(
        'Lists' => array(
            'key_from' => 'id',
            'key_through_from' => 'id_list', 
            'table_through' => 'ListsHaveSongs', 
            'key_through_to' => 'id_song', 
            'model_to' => 'Model_Lists',
            'key_to' => 'id',
            'cascade_save' => true,
            'cascade_delete' => true,
        )
    );
}