<?php
namespace Fuel\Migrations;

class ListsHaveSongs
{
		
    function up()
    {	
    	try
    	{
	        \DBUtil::create_table(
	    		'ListsHaveSongs',
	    		array(
	    		    'id_list' => array('constraint' => 11, 'type' => 'int'),
	    		    'id_song' => array('constraint' => 11, 'type' => 'int'),
	    		),
	    		array('id_list', 'id_song'), false, 'InnoDB', 'utf8_general_ci',
	    		array(
                    array(
                        'constraint' => 'foreingKeyLists_SongsToList',
                        'key' => 'id_list',
                        'reference' => array(
                            'table' => 'Lists',
                            'column' => 'id',
                        ),
                        'on_update' => 'CASCADE',
                        'on_delete' => 'RESTRICT'
                    ),
                    array(
                        'constraint' => 'foreingKeyLists_SongsToSong',
                        'key' => 'id_song',
                        'reference' => array(
                            'table' => 'Songs',
                            'column' => 'id',
                        ),
                        'on_update' => 'CASCADE',
                        'on_delete' => 'RESTRICT'
                    ),
                )
			);
    	}
    	catch(\Database_Exception $e)
		{
		   echo 'Lista ya creada'; 
		}
    }

    function down()
    {
       \DBUtil::drop_table('ListsHaveSongs');
    }

}